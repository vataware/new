<?php

class Airroute extends Eloquent {

	protected $table = 'routes';
	public $timestamps = true;
	protected $softDelete = false;

	public function departure()
	{
		return $this->belongsTo('Airport', 'departure_id');
	}

	public function arrival()
	{
		return $this->belongsTo('Airport', 'arrival_id');
	}

	public function scopeDepartFrom($query, $airport)
	{
		return $query->whereDepartureId($airport);
	}

	public function scopeArriveAt($query, $airport)
	{
		return $query->whereArrivalId($airport);
	}

}