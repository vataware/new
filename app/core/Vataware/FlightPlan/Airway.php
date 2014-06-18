<?php namespace Vataware\FlightPlan;

class Airway extends \Eloquent {
	
	protected $table = 'navdata_airways';
	public $timestamps = false;

	function waypoints() {
		return $this->hasMany('Vataware\FlightPlan\Waypoint','ident','ident');
	}

}