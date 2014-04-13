<?php

class AirportController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$airports = Airport::with('country','departures','arrivals')->orderBy('country_id')->orderBy('city')->orderBy('id')->paginate(100);

		$this->autoRender(compact('airports'), 'Airports');
	}

	function show(Airport $airport) {


		$this->autoRender(compact('airport'), $airport->id . ' - ' . $airport->name);
	}

}