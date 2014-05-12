<?php

class Search {
	
	protected $query;

	function __construct($query) {
		$this->query = $query;
	}

	function quick($type) {
		if(method_exists($this, $type))
			return call_user_func_array(array($this, $type), array_slice(func_get_args(),1));

		return false;
	}

	function pilot() {
		$pilot = Pilot::find($this->query);
		if(!is_null($pilot)) {
			return Redirect::route('pilot.show', $pilot->vatsim_id);
		}

		return false;
	}

	function airport() {
		$airport = Airport::whereIcao($this->query)->first();
		if(!is_null($airport)) {
			return Redirect::route('airport.show', $airport->icao);
		}

		return false;
	}

	function airportIata() {
		$airport = Airport::whereIata($this->query)->first();
		if(!is_null($airport)) {
			return Redirect::route('airport.show', $airport->icao);
		}

		return false;
	}

	function airline() {
		$airline = Airline::whereIcao($this->query)->first();
		if(!is_null($airline)) {
			return Redirect::route('airline.show', $airline->icao);
		}

		return false;
	}

	function citypair($matches) {
		$departure = strtoupper($matches[1]);
		$arrival = strtoupper($matches[2]);

		if(!is_null(Airport::find($departure)) && !is_null(Airport::find($arrival))) {
			return Redirect::route('citypair', array('departure' => $departure, 'arrival' => $arrival));
		}

		return false;
	}

	function callsign() {
		$flight = Flight::whereCallsign($this->query)->whereIn('state', array(0, 1, 3, 4))->first();

		if(!is_null($flight)) {
			return Redirect::route('flight.show', $flight->id);
		}

		return false;
	}

}