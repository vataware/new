<?php

class AirlineChange extends Eloquent {

	function airline() {
		return $this->belongsTo('Airline');
	}

	function user() {
		return $this->belongsTo('User','user_id','vatsim_id');
	}

}