<?php namespace Vataware\FlightPlan;

class Navdata extends \Eloquent {
	
	protected $table = 'navdata';
	public $timestamps = false;

	function getTypeAttribute($value) {
		switch($value) {
			case 2:
				return 'NDB';
			case 3:
				return 'VOR';
			case 4:
				return 'DME';
			case 5:
				return 'FIX';
			case 6:
				return 'TRACK';
			default:
				return $value;
		}
	}

	function getIconAttribute() {
		switch($this->getOriginal('type')) {
			case 2:
			case 3:
			case 4:
				return 'vor';
			case 5:
				return 'fix';
		}
	}

	function getAnchorAttribute() {
		switch($this->getOriginal('type')) {
			case 2:
			case 3:
			case 4:
				return '10,10';
			case 5:
				return '6,7.5';
		}
	}

}