<?php

class AirportChange extends Eloquent {

	function airport() {
		return $this->belongsTo('Airport');
	}

	function user() {
		return $this->belongsTo('User','user_id','vatsim_id');
	}

}