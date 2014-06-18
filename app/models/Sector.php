<?php

class Sector extends Eloquent {
	
	protected $table = 'sectors';
	// protected $primaryKey = 'code';
	public $timestamps = false;

	function aliases() {
		return $this->hasMany('SectorAlias');
	}

	function segments() {
		return $this->hasMany('SectorSegment');
	}

	function getPolygonAttribute() {
		$polygon = array();
		if($this->segments->count() > 0) {
			foreach($this->segments as $segment) {
				$polygon[] = 'new google.maps.LatLng(' . $segment->lat . ',' . $segment->lon .')';
			}
			$polygon[] = $polygon[0];
		}
		return '[' . implode(",",$polygon) . ']';
	}

}