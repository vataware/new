<?php

class Flight extends Eloquent {

	protected $table = 'flights';
	public $timestamps = true;
	protected $softDelete = false;
	protected $dates = ['departure_time','arrival_time'];
	protected $appends = ['duration'];

	public function aircraft()
	{
		return $this->hasMany('Aircraft','code','aircraft_id');
	}

	public function departure()
	{
		return $this->belongsTo('Airport', 'departure_id');
	}

	public function arrival()
	{
		return $this->belongsTo('Airport', 'arrival_id');
	}

	public function pilot()
	{
		return $this->belongsTo('Pilot','vatsim_id','vatsim_id');
	}

	public function getDurationAttribute()
	{
		if(is_null($this->departure_time) || is_null($this->arrival_time)) return 'Unknown';
		$hours = $this->departure_time->diffInHours($this->arrival_time);
		$minutes = $this->departure_time->diffInMinutes($this->arrival_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . ':' . str_pad($minutes,2,'0',STR_PAD_LEFT);
	}

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
		return $this->hasMany('Position')->orderBy('time','asc');
	}

	public function lastPosition()
	{
		return $this->hasOne('Position')->orderBy('time','desc');
	}

	public function getStatusAttribute() {
		switch($this->state) {
			case 0:
				return 'Departing...';
			case 1:
			case 3:
				return 'Airborne';
			case 2:
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

}