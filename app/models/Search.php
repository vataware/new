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
		$airport = Airport::find($this->query);
		if(!is_null($airport)) {
			return Redirect::route('airport.show', $airport->id);
		}

		return false;
	}

	function citypair($matches) {
		$departure = $matches[1];
		$arrival = $matches[2];

		if(!is_null(Airport::find($departure)) && !is_null(Airport::find($arrival))) {
			return Redirect::route('citypair', array('departure' => $departure, 'arrival' => $arrival));
		}

		return false;
	}

}