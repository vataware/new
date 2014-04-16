<?php

class AirportController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$airports = Airport::with('country','departures','arrivals')->orderBy('country_id')->orderBy('city')->orderBy('id')->paginate(100);

		$this->autoRender(compact('airports'), 'Airports');
	}

	function show(Airport $airport) {
		$departures = $airport->departures()->with('pilot','arrival','arrival.country')->whereIn('state',array(0,4))->where('arrival_id','!=','')->get();
		$arrivals = $airport->arrivals()->with('pilot','departure','departure.country')->whereIn('state',array(0,4))->get();

		$this->autoRender(compact('airport','departures','arrivals'), $airport->id . ' - ' . $airport->name);
	}

}