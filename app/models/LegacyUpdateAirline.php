<?php

class LegacyUpdateAirline {
	
	protected $airlines = null;
	protected $registrations = null;

	function fire($job, $data) {
		Log::info('queue:legacy - started airline ' . $data['airline'] . ' for year ' . $data['year']);

		$flights = Flight::where('callsign','LIKE',$data['airline'] . '%')->whereState(2)->where('startdate','LIKE',$data['year'] . '%')->whereProcessed(false)->get();

		$totalFlights = $flights->count();
		Log::info('queue:legacy[' . $data['airline'] . '] - flights: ' . $flights->count());

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

			unset($flight, $distance, $previous);
		}

		unset($flights);

		Log::info('queue:legacy - finished airline ' . $data['airline'] . ' (' . $data['year'] . ') for ' . $totalFlights . ' flights');

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