<?php

class Airport extends Eloquent {

	protected $table = 'airports';
	public $timestamps = false;
	protected $softDelete = false;

	public function country()
	{
		return $this->belongsTo('Country');
	}

	public function departures()
	{
		return $this->hasMany('Flight', 'departure_id', 'icao');
	}

	public function arrivals()
	{
		return $this->hasMany('Flight', 'arrival_id', 'icao');
	}

	public function runways()
	{
		return $this->hasMany('Runway', 'airport_id', 'icao');
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

	public function getTypeAttribute($value) {
		switch($value) {
			case 'large_airport':
				return 'Large Airport';
			case 'medium_airport':
				return 'Medium Airport';
			case 'small_airport':
				return 'Small Airport';
			case 'heliport':
				return 'Heliport';
			case 'seaplane_base':
				return 'Seaplane Base';
			case 'balloonport':
				return 'Balloon Port';
			case 'closed':
				return 'Closed';
			default:
				return 'Other';
		}
	}

}