<?php

class Position extends Eloquent {

	protected $table = 'positions';
	public $timestamps = false;
	protected $softDelete = false;
	protected $dates = ['time'];

	public function flight()
	{
		return $this->belongsTo('Flight');
	}

	public function ping()
	{
		return $this->belongsTo('Update');
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