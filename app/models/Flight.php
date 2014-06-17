<?php

class Flight extends Eloquent {

	protected $table = 'flights';
	public $timestamps = true;
	protected $softDelete = true;
	protected $dates = ['departure_time','arrival_time','deleted_at'];

	public function aircraft()
	{
		return $this->hasMany('Aircraft','code','aircraft_id');
	}

	public function departure()
	{
		return $this->belongsTo('Airport', 'departure_id', 'icao');
	}

	public function arrival()
	{
		return $this->belongsTo('Airport', 'arrival_id', 'icao');
	}

	public function pilot()
	{
		return $this->belongsTo('Pilot','vatsim_id','vatsim_id');
	}

/*	public function getDurationAttribute()
	{
		if(is_null($this->departure_time)) return 'Unknown';
		$time = ($this->state == 1 || $this->state == 3) ? Carbon::now() : $this->arrival_time;
		$hours = $this->departure_time->diffInHours($time);
		$minutes = $this->departure_time->diffInMinutes($time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . ':' . str_pad($minutes,2,'0',STR_PAD_LEFT);
	}*/

	public function departureCountry()
	{
		return $this->belongsTo('Country', 'departure_country_id');
	}

	public function arrivalCountry()
	{
		return $this->belongsTo('Country', 'arrival_country_id');
	}

	public function airline()
	{
		return $this->hasOne('Airline','icao','airline_id');
	}

	public function privateCountry()
	{
		return $this->belongsTo('Country','airline_id');
	}

	public function positions()
	{
		return $this->hasMany('Position');
	}

	public function getMapsPositionsAttribute() {
		$positions = [];

		foreach($this->positions as $position) {
			$positions[] = 'new google.maps.LatLng(' . $position->lat . ', ' . $position->lon . ')';
		}

		return implode(",", $positions);
	}

	public function getProfileElevationsAttribute() {
		$positions = [];

		foreach($this->positions as $position) {
			$positions[] = '[' . $position->time->format('U')*1000 . ',' . $position->ground_elevation . ']';
		}

		return implode(",", $positions);
	}

	public function getProfileAltitudeAttribute() {
		$positions = [];

		foreach($this->positions as $position) {
			$positions[] = '[' . $position->time->format('U')*1000 . ',' . $position->altitude . ']';
		}

		return implode(",", $positions);
	}

	public function getProfileSpeedAttribute() {
		$positions = [];

		foreach($this->positions as $position) {
			$positions[] = '[' . $position->time->format('U')*1000 . ',' . $position->speed . ']';
		}

		return implode(",", $positions);
	}

	public function getMapsColoursAttribute() {
		$positions = [];

		foreach($this->positions as $position) {
			$positions[] = '#' . altitudeColour($position->altitude,'',true);
		}

		return $positions;
	}

	public function lastPosition()
	{
		return $this->hasOne('Position')->orderBy('id','desc');
	}

	public function getStatusAttribute() {
		switch($this->state) {
			case 0:
				return 'Departing...';
			case 1:
			case 3:
				return 'Airborne';
			case 2:
			case 5:
				return 'Arrived';
			case 4:
				return 'Preparing...';
		}
	}

	public function getStatusIconAttribute() {
		switch($this->state) {
			case 0:
			case 4:
				return 'departing';
			case 1:
			case 3:
				return 'airborne';
			case 2:
			case 5:
				return 'arrived';
		}
	}

	public function getAltitudeAttribute($value) {
		if(starts_with($value,'FL') || starts_with($value,'F')) {
			return filter_var($value, FILTER_SANITIZE_NUMBER_INT)*100;
		} elseif(strlen($value) <= 3) {
			return $value*100;
		} else {
			return $value;
		}
	}

	public function getTotalTimeAttribute() {
		if(is_null($this->arrival_time)) return $this->traveled_time;

		$hours = $this->arrival_time->diffInHours($this->departure_time);
		$minutes = $this->arrival_time->diffInMinutes($this->departure_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	public function getTotalEETAttribute() {
		if(is_null($this->arrival_time)) return $this->traveled_time;

		$hours = $this->arrival_time->diffInHours($this->departure_time);
		$minutes = $this->arrival_time->diffInMinutes($this->departure_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return str_pad($hours,2,'0',STR_PAD_LEFT) . str_pad($minutes,2,'0',STR_PAD_LEFT);
	}

	public function getTraveledTimeAttribute() {
		if(is_null($this->departure_time)) return null;
		$now = Carbon::now();
		$hours = $now->diffInHours($this->departure_time);
		$minutes = $now->diffInMinutes($this->departure_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	public function getTogoTimeAttribute() {
		if(is_null($this->arrival_time)) return null;
		$now = Carbon::now();
		$hours = $now->diffInHours($this->arrival_time);
		$minutes = $now->diffInMinutes($this->arrival_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	function isAirline($airline) {
		$this->attributes['callsign_type'] = 1;
		$this->attributes['airline_id'] = $airline;
	}

	function isPrivate($registration) {
		$this->attributes['callsign_type'] = 2;
		$this->attributes['airline_id'] = $registration;
	}

	function getNmAttribute() {
		return $this->distance * 0.54;
	}

	function getHoursAttribute() {
		return floor($this->duration/60);
	}

	function getMinutesAttribute() {
		return str_pad(($this->duration - ($this->hours * 60)),2,'0',STR_PAD_LEFT);
	}

}