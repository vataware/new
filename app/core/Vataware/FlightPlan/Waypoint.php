<?php namespace Vataware\FlightPlan;

class Waypoint extends \Eloquent {
	
	protected $table = 'navdata_waypoints';
	public $timestamps = false;

	function getTypeAttribute($value) {
		switch($value) {
			case 'N':
				return 'NDB';
			case 'V':
				return 'VOR';
			case 'D':
				return 'DME';
			case 'F':
				return 'FIX';
			case 'T':
				return 'TRACK';
			default:
				return $value;
		}
	}

	function getIconAttribute() {
		switch($this->getOriginal('type')) {
			case 'N':
			case 'V':
			case 'D':
				return 'vor';
			case 'F':
				return 'fix';
		}
	}

	function getAnchorAttribute() {
		switch($this->getOriginal('type')) {
			case 'N':
			case 'V':
			case 'D':
				return '10,10';
			case 'F':
				return '6,7.5';
		}
	}

	function getFreqAttribute($value) {
		if($this->getOriginal('type') == 'V') {
			return number_format($value/100, 2);
		}
		return $value;
	}

}