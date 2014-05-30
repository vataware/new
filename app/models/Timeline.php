<?php

class Timeline extends Eloquent {
	
	function user() {
		return $this->belongsTo('Team','user_id','vatsim_id');
	}

	function getItemAttribute() {
		return View::make('admin._timeline.' . $this->type, array('timeline' => $this));
	}

	function getActivityAttribute() {
		return json_decode($this->body);
	}

	function setActivityAttribute($value) {
		$this->attributes['body'] = json_encode($value);
	}

}