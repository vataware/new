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

	function getRatingAttribute() {
		switch($this->rating_id) {
			case 1:
				return 'Pilot/Observer';
			case 2:
				return 'Student';
			case 3:
			case 4:
				return 'Senior Student';
			case 5:
				return 'Controller';
			case 6:
			case 7:
				return 'Senior Controller';
			case 8:
				return 'Instructor';
			case 9:
			case 10:
				return 'Senior Instructor';
			case 11:
				return 'Supervisor';
			case 12:
				return 'Administrator';
			default:
				return 'Unknown';
		}
	}

}