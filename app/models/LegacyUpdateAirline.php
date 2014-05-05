<?php

class LegacyUpdateAirline {
	
	protected $registrations = null;

	protected $take = 90;

	function fire($job, $data) {		
		Log::info('queue:legacy[' . $job->getJobId() . '] - started airline ' . $data['airline']);

		$running = Cache::get('legacy.airlines.' . $data['airline'], false);
		if($running != $job->getJobId() && $running !== false) {
			Log::info('queue:legacy[' . $job->getJobId() . '] - already running ' . $data['airline']);
			Cache::forget('legacy.airlines.' . $data['airline']);
			$job->delete();
			return;
		} else {
			Cache::forever('legacy.airlines.' . $data['airline'], $job->getJobId());
		}

		$flights = Flight::where('callsign','LIKE',$data['airline'] . '%')->whereState(2)->whereProcessed(false)->take($this->take)->get();

		$totalFlights = $flights->count();
		Log::info('queue:legacy[' . $job->getJobId() . '] - flights: ' . $flights->count());

		if($flights->count() == 0) {
			Log::info('queue:legacy[' . $job->getJobId() . '] - no more flights for ' . $data['airline']);
			$airline = Airline::whereIcao($data['airline'])->first();
			$airline->duration = $airline->flights()->whereState(2)->sum('duration');
			$airline->save();
			return $this->finishJob($job, $data['airline']);
		}

		$job->delete();

		if($job->attempts() > 1) {
			return;
		}

		foreach($flights as $i => $flight) {
			$callsign = str_replace('-','',strtoupper($flight->callsign));
			if(preg_match('/^' . $data['airline'] . '[0-9]{1,5}[A-Z]{0,2}$/', $flight->callsign)) { // Airline
				$flight->isAirline($data['airline']);
			} elseif(!is_null($registration = $this->getRegistrations($callsign))) {
				$flight->isPrivate($registration->country_id);
				unset($registration);
			}

			if(!is_null($flight->departure_time) && !is_null($flight->arrival_time)) {
				$duration = $this->duration($flight->departure_time, $flight->arrival_time);
				$flight->duration = $duration;
				unset($duration);
			}

			$distance = 0;
			foreach($flight->positions as $key => $position) {
				if($key > 0) $distance += $this->distance($position->lat, $position->lon, $previous->lat, $previous->lon);
				$previous = $position;
			}
			$flight->distance = $distance;
			$flight->processed = true;
			$flight->save();

			unset($flight, $distance, $previous, $i);
		}

		unset($flights);

		Log::info('queue:legacy[' . $job->getJobId() . '] - finished airline ' . $data['airline']);
		Cache::forget('legacy.airlines.' . $data['airline']);
		Queue::push('LegacyUpdateAirline', $data, 'legacy');
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

	function finishJob($job, $airline) {
		$airlines = Cache::get('legacy.airlines', array());
		$airlines[] = $airline;
		Cache::forever('legacy.airlines', $airlines);

		$job->delete();
		return;
	}

}