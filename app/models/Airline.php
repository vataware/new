<?php

class Airline extends Eloquent {

	protected $table = 'airlines';
	public $timestamps = true;
	protected $softDelete = false;

	public function flights()
	{
		return $this->hasMany('Flight');
	}

}