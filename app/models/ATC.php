<?php

class ATC extends Eloquent {

	protected $table = 'atc';
	public $timestamps = true;
	protected $softDelete = true;
	protected $dates = ['time','start','end'];
	protected $appends = ['facility'];

	function getRatingAttribute() {
		switch($this->rating_id) {
			case 1:
				return 'Observer';
			case 2:
				return 'Student';
			case 3:
				return 'Student 2';
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

	function getFacilityAttribute() {
		switch($this->facility_id) {
			case 0:
				return 'Observer';
			case 1:
				return 'Flight Service Station';
			case 2:
				return 'Clearance Delivery';
			case 3:
				return 'Ground';
			case 4:
				return 'Tower';
			case 5:
				return 'Approach/Departure';
			case 6:
				return 'Center';
			case 99:
				return 'ATIS';
			default:
				return 'Unknown';
		}
	}

	function getFacilityAbbrAttribute() {
		switch($this->facility_id) {
			case 0:
				return 'OBS';
			case 1:
				return 'FSS';
			case 2:
				return 'DEL';
			case 3:
				return 'GND';
			case 4:
				return 'TWR';
			case 5:
				return 'APP/DEP';
			case 6:
				return 'CTR';
			case 99:
				return 'ATIS';
			default:
				return 'Unknown';
		}
	}

	public function getDurationHumanAttribute() {
		if(!is_null($this->end)) {
			$hours = floor($this->duration/60);
			$minutes = $this->duration % 60;
		} else {
			$now = Carbon::now();
			$hours = $now->diffInHours($this->start);
			$minutes = $now->diffInMinutes($this->start);
			$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		}
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	public function getFrequencyAttribute($value) {
		if(is_null($value)) return null;
		if(!is_numeric($value)) return $value;
		return number_format($value, 3, '.', '');
	}

	public function pilot() {
		return $this->belongsTo('Pilot','vatsim_id','vatsim_id');
	}

	public function airport()
	{
		return $this->belongsTo('Airport','airport_id','icao');
	}

	public function sector()
	{
		return $this->belongsTo('Sector','sector_id','code');
	}

}