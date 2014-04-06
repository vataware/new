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

}