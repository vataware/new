<?php

class Team extends Eloquent {
	
	protected $table = 'team';
	protected $softDelete = true;

	function getMediaAttribute() {
		$media = [];
		if(!is_null($this->email))
			$media[] = '<a href="mailto:' . HTML::obfuscate($this->email) . '">email</a>';

		if(!is_null($this->facebook))
			$media[] = '<a href="https://www.facebook.com/' . $this->facebook . '">facebook</a>';

		if(!is_null($this->twitter))
			$media[] = '<a href="https://twitter.com/' . $this->twitter . '">twitter</a>';

		return implode(' &bull; ', $media);
	}

	function getPhotoAttribute() {
		if(File::exists(public_path() . '/assets/images/team/' . Str::slug($this->name) . '.jpg')) {
			return asset('assets/images/team/' . Str::slug($this->name) . '.jpg');
		}
		return false;
	}

}