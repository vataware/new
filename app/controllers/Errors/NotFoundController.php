<?php namespace Errors;

use BaseController, Country, Airline, Airport;

class NotFoundController extends BaseController {
	
	protected $layout = 'layouts.master';

	function airport($parameters) {
		extract($parameters);

		$countries = Country::orderBy('country')->lists('country','id');
		$exists = !is_null(Airport::whereIcao($airport)->first());

		$this->autoRender(compact('airport', 'countries', 'exists'));
	}

	function airline($parameters) {
		extract($parameters);

		$exists = !is_null(Airline::whereIcao($airline)->first());

		$this->autoRender(compact('airline','exists'));
	}

}