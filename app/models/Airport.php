<?php

class Airport extends Eloquent {

	protected $table = 'airports';
	public $timestamps = true;
	protected $softDelete = false;

	public function getIcaoAttribute() {
		return $this->id;
	}

	public function country()
	{
		return $this->belongsTo('Country');
	}

	public function departures()
	{
		return $this->hasMany('Flight', 'departure_id');
	}

	public function arrivals()
	{
		return $this->hasMany('Flight', 'arrival_id');
	}

	public function getLatSAttribute() {
		$direction = ($this->lat > 0) ? 'N' : 'S';

		$lat = abs($this->lat);
		$minutes = ($lat - floor($lat)) * 60;
		$seconds = ($minutes - floor($minutes)) * 60;

		return floor($lat) . '&deg; ' . floor($minutes) . '\' ' . floor($seconds) . '" ' . $direction;
	}

	public function getLonSAttribute() {
		$direction = ($this->lon > 0) ? 'E' : 'W';

		$lon = abs($this->lon);
		$minutes = ($lon - floor($lon)) * 60;
		$seconds = ($minutes - floor($minutes)) * 60;

		return floor($lon) . '&deg; ' . floor($minutes) . '\' ' . floor($seconds) . '" ' . $direction;
	}

}