<?php

class Registration extends Eloquent {

	protected $table = 'registrations';
	public $timestamps = false;
	protected $softDelete = false;

	public function country()
	{
		return $this->belongsTo('Country');
	}

}