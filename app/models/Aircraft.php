<?php

class Aircraft extends Eloquent {

	protected $table = 'aircraft';
	public $timestamps = true;
	protected $softDelete = false;

	function getNameAttribute() {
		return $this->manufacturer . ' ' . $this->model;
	}

}