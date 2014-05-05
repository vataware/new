<?php

class AirportController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$airports = Airport::with('country','departures','arrivals')->orderBy('country_id')->orderBy('city')->orderBy('id')->paginate(100);

		$this->autoRender(compact('airports'), 'Airports');
	}

	function show(Airport $airport) {
		$departures = $airport->departures()->with('pilot','arrival','arrival.country')->whereIn('state',array(0,1,4))->whereMissing(false)->where('arrival_id','!=','')->get();
		$arrivals = $airport->arrivals()->with('pilot','departure','departure.country')->whereIn('state',array(1,3))->whereMissing(false)->get();

		try {
			$metar = file_get_contents('http://weather.noaa.gov/pub/data/observations/metar/stations/' . strtoupper($airport->icao) . '.TXT');
		} catch(ErrorException $e) {
			$metar = null;
		}

		try {
			$taf = file_get_contents('http://weather.noaa.gov/pub/data/forecasts/taf/stations/' . strtoupper($airport->icao) . '.TXT');
		} catch(ErrorException $e) {
			$taf = null;
		}

		$this->autoRender(compact('airport','departures','arrivals','metar','taf'), $airport->icao . ' - ' . $airport->name);
	}

}