<?php

class LegacyUpdate {
	
	protected $airlines = null;
	protected $registrations = null;

	function fire($job, $vatsim_id) {
		$pilot = Pilot::whereVatsimId($vatsim_id)->first();
		if($pilot->processing == 1) {
			$job->delete();
			return;
		}

		try {
			$it = new XmlIterator\XmlIterator('https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=' . $vatsim_id, 'user');
			$official = iterator_to_array($it)[0];

			$pilot->name = $official['name_first'] . ' ' . $official['name_last'];
			$pilot->rating_id = $official['rating'];
		} catch(ErrorException $e) {}

		$newFlights = array();

		$flights = Flight::whereVatsimId($pilot->vatsim_id)->whereState(2)->get();
		$totalDistance = 0;
		$totalDuration = 0;
		$totalFlights = $flights->count();
		foreach($flights as $flight) {
			if($flight->processed == 1) {
				$totalDistance += $flight->distance;
				$totalDuration += $flight->duration;
			} else {
				$callsign = str_replace('-','',strtoupper($flight->callsign));
				if(!is_null($airline = $this->getAirlines($callsign))) { // Airline
					$flight->isAirline($airline->icao);
					unset($airline);
				} elseif(!is_null($registration = $this->getRegistrations($callsign))) {
					$flight->isPrivate($registration->country_id);
					unset($registration);
				}

				if(!is_null($flight->departure_time) && !is_null($flight->arrival_time)) {
					$duration = $this->duration($flight->departure_time, $flight->arrival_time);
					$flight->duration = $duration;
					$totalDuration += $duration;
					unset($duration);
				}

				$distance = 0;
				foreach($flight->positions as $key => $position) {
					if($key > 0) $distance += $this->distance($position->lat, $position->lon, $previous->lat, $previous->lon);
					
					$previous = $position;
				}
				$flight->distance = $distance;
				// $flight->processed = true;
				// $flight->save();
				if(!is_nan($distance)) $totalDistance += $distance;
				unset($distance, $previous);
			}

			$newFlights[] = array('id' => $flight->id, 'duration' => $flight->duration, 'distance' => $flight->distance, 'airline_id' => $flight->airline_id, 'callsign_type' => $flight->callsign_type);
			unset($flight);
		}

		unset($flights);

		Log::info('queue:legacy[' . $job->getJobId() . '] - processed flights');

		DB::statement("create temporary table if not exists flights_temp (
			`id` int(10) unsigned NOT NULL,
			`callsign_type` tinyint(1) NOT NULL DEFAULT '0',
			`airline_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
			`duration` smallint(6) NOT NULL DEFAULT '0',
			`distance` smallint(6) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		)");

		Log::info('queue:legacy[' . $job->getJobId() . '] - created temp table flights');

		$remaining = count($newFlights);
		$step = 0;
		do {
			Log::info('queue:legacy[' . $job->getJobId() . '] - inserted flights - ' . $remaining);
			DB::table('flights_temp')->insert(array_slice($newFlights, 100 * $step, 100));
			$remaining -= 100;
			$step++;
		} while($remaining > 0);

		Log::info('queue:legacy[' . $job->getJobId() . '] - inserted flights - done');

		DB::statement("update flights dest, flights_temp src set
			dest.callsign_type = src.callsign_type,
			dest.airline_id = src.airline_id,
			dest.duration = src.duration,
			dest.distance = src.distance,
			dest.processed = 1
		where dest.id = src.id
		");

		Log::info('queue:legacy[' . $job->getJobId() . '] - updated flights');

		$atcs = ATC::whereVatsimId($vatsim_id)->whereNotNull('end')->get();
		$totalDurationAtc = 0;
		$totalAtc = $atcs->count();
		$newAtc = array();
		foreach($atcs as $atc) {
			if($atc->processed) {
				if($atc->facility_id != 99) $totalDurationAtc += $atc->duration;
				else $totalAtc--;
			} else {
				$atc->facility_id = (ends_with($atc->callsign, '_ATIS')) ? 99 : $atc->facility_id;

				$duration = $this->duration($atc->start, $atc->end);
				$atc->duration = $duration;
				if($atc->facility_id != 99) $totalDurationAtc += $duration;

				if($atc->facility_id < 6) {
					$airport = Airport::select('icao')->whereIcao(explode('_',$atc->callsign)[0])->orWhere('iata','=',explode('_',$atc->callsign)[0])->pluck('icao');
					$atc->airport_id = (is_null($airport)) ? null : $airport;
					unset($airport);
				} elseif($atc->facility_id == 6) {
					$sector = SectorAlias::select('sectors.code')->where('sector_aliases.code','=',explode('_', $atc->callsign)[0])->join('sectors','sector_aliases.sector_id','=','sectors.id')->pluck('code');
					$atc->sector_id = (is_null($sector)) ? null : $sector;
					unset($sector);
				} else {
					$totalAtc--;
				}

				// $atc->processed = true;
				// $atc->save();
			}

			$newAtc[] = array('id' => $atc->id, 'airport_id' => $atc->airport_id, 'sector_id' => $atc->sector_id, 'duration' => $atc->duration, 'facility_id' => $atc->facility_id);

			unset($atc);
		}
		unset($atcs);

		Log::info('queue:legacy[' . $job->getJobId() . '] - processed atc');

		DB::statement("create temporary table if not exists atc_temp (
			`id` int(10) unsigned NOT NULL,
			`facility_id` smallint(6) unsigned NOT NULL,
			`airport_id` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
			`sector_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
			`duration` smallint(6) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		)");

		Log::info('queue:legacy[' . $job->getJobId() . '] - created temp table atc');

		$remaining = count($newAtc);
		$step = 0;
		do {
			Log::info('queue:legacy[' . $job->getJobId() . '] - inserted atc - ' . $remaining);
			DB::table('atc_temp')->insert(array_slice($newAtc, 100 * $step, 100));
			$remaining -= 100;
			$step++;
		} while($remaining > 0);

		Log::info('queue:legacy[' . $job->getJobId() . '] - inserted atc - done');

		DB::statement("update atc dest, atc_temp src set
			dest.duration = src.duration,
			dest.facility_id = src.facility_id,
			dest.airport_id = src.airport_id,
			dest.sector_id = src.sector_id,
			dest.processed = 1
		where dest.id = src.id
		");

		Log::info('queue:legacy[' . $job->getJobId() . '] - updated atc');

		$pilot->processing = 1;
		$pilot->distance = $totalDistance;
		$pilot->duration = $totalDuration;
		$pilot->counter = $totalFlights;
		$pilot->counter_atc = $totalAtc;
		$pilot->duration_atc = $totalDurationAtc;

		$pilot->save();
		
		$job->delete();
	}

	function duration($start, $now) {
		return $start->diffInMinutes($now);
	}

	function distance($lat1, $lon1, $lat2, $lon2) {
		return acos(sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon1) - deg2rad($lon2))) * 6371;
	}

	function getRegistrations($callsign = null) {
		if(is_null($this->registrations)) $this->registrations = Registration::get()->each(function($registration) {
			$registration->prefix = str_replace('-', '', $registration->prefix);
			if(!$registration->regex) $registration->prefix .= '.*';
		});

		if(!is_null($callsign)) 
			return $this->registrations->first(function($key, $registration) use ($callsign) {
				return preg_match('/^' . $registration->prefix . '$/', $callsign);
			});

		return $this->registrations;
	}

	function getAirlines($callsign = null) {
		if(is_null($this->airlines)) $this->airlines = Airline::get();

		if(!is_null($callsign)) 
			return $this->airlines->first(function($key, $airline) use ($callsign) {
				return preg_match('/^' . $airline->icao . '[0-9]{1,5}[A-Z]{0,2}$/', $callsign);
			});

		return $this->airlines;
	}

}