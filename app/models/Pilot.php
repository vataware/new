<?php

class Pilot extends Eloquent {

	protected $table = 'pilots';
	protected $primaryKey = 'vatsim_id';
	public $timestamps = true;
	protected $softDelete = false;

	public function flights()
	{
		return $this->hasMany('Flight','vatsim_id');
	}

}