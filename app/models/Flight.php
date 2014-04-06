<?php

class Flight extends Eloquent {

	protected $table = 'flights';
	public $timestamps = true;
	protected $softDelete = false;
	protected $dates = ['departure_time','arrival_time'];

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
		return $this->belongsTo('Airline');
	}

	public function positions()
	{
		return $this->hasMany('Position');
	}

}