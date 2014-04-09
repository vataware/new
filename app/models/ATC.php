<?php

class ATC extends Eloquent {

	protected $table = 'atc';
	public $timestamps = true;
	protected $softDelete = false;

	public function rating()
	{
		return $this->belongsTo('Rating');
	}

}