<?php

class Airline extends Eloquent {

	protected $table = 'airlines';
	public $timestamps = true;
	protected $softDelete = false;

	public function flights()
	{
		return $this->hasMany('Flight','airline_id','icao')->whereCallsignType(1);
	}

	function getWebsiteAttribute($value) {
		if(is_null($value))
			return null;

		if(preg_match('#https?://#', $value) === 0)
			return 'http://' . $value;

		return $value;
	}

	public function getWebsiteCleanAttribute() {
		return str_replace(array('http://www.','https://www.','http://','https://'),'',$this->website);
	}

}