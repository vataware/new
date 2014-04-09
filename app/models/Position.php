<?php

class Position extends Eloquent {

	protected $table = 'positions';
	public $timestamps = true;
	protected $softDelete = false;

	public function flight()
	{
		return $this->belongsTo('Flight');
	}

	public function ping()
	{
		return $this->belongsTo('Update');
	}

}