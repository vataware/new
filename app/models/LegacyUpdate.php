<?php

class LegacyUpdate {
	
	protected $airlines = null;
	protected $registrations = null;

	function fire($job, $vatsim_id) {
		$pilot = Pilot::whereVatsimId($vatsim_id)->first();
		Log::info('queue:legacy - started pilot ' . $vatsim_id);

		try {
			$it = new XmlIterator\XmlIterator('https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=' . $vatsim_id, 'user');
			$official = iterator_to_array($it)[0];

			$pilot->name = $official['name_first'] . ' ' . $official['name_last'];
			$pilot->rating_id = $official['rating'];
		} catch(ErrorException $e) {
			
		}

		$flights = Flight::with('positions')->whereVatsimId($pilot->vatsim_id)->whereState(2)->get();
		$totalDistance = 0;
		$totalDuration = 0;
		$totalFlights = $flights->count();

		foreach($flights as $flight) {
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
			$flight->save();
			if(!is_nan($distance)) $totalDistance += $distance;
			unset($flight, $distance, $previous);
		}

		unset($flights);

		$atcs = ATC::whereVatsimId($vatsim_id)->whereNotNull('end')->get();
		$totalDurationAtc = 0;
		$totalAtc = $atcs->count();

		foreach($atcs as $atc) {
			$atc->facility_id = (ends_with($atc->callsign, '_ATIS')) ? 99 : $atc->facility_id;

			$duration = $this->duration($atc->start, $atc->end);
			$atc->duration = $duration;
			

			if($atc->facility_id < 6) {
				$airport = Airport::select('icao')->whereIcao(explode('_',$atc->callsign)[0])->orWhere('iata','=',explode('_',$atc->callsign)[0])->pluck('icao');
				$atc->airport_id = (is_null($airport)) ? null : $airport;
				unset($airport);
			} elseif($atc->facility_id == 6) {
				$sector = SectorAlias::select('sectors.code')->where('sector_aliases.code','=',explode('_', $atc->callsign)[0])->join('sectors','sector_aliases.sector_id','=','sectors.id')->pluck('code');
				$atc->sector_id = (is_null($sector)) ? null : $sector;
				unset($sector);
			} else {
				$totalDurationAtc += $duration;
				$totalAtc--;
			}

			$atc->save();

			unset($atc);
		}


		unset($atcs);

		$pilot->processing = 1;
		$pilot->distance = $totalDistance;
		$pilot->duration = $totalDuration;
		$pilot->counter = $totalFlights;
		$pilot->counter_atc = $totalAtc;
		$pilot->duration_atc = $totalDurationAtc;

		$pilot->save();

		Log::info('queue:legacy - finished pilot ' . $vatsim_id . ' for ' . $totalFlights . ' flights and ' . $totalAtc . ' ATC');

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