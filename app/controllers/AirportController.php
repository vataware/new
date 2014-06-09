<?php

class AirportController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$airports = Airport::with('country','departures','arrivals')->orderBy('country_id')->orderBy('city')->orderBy('id')->paginate(100);

		$this->autoRender(compact('airports'), 'Airports');
	}

	function store() {
		$rules = array(
			'icao' => 'alpha_num|required',
			'iata' => 'alpha_num',
			'name' => 'required',
			'city' => 'required',
			'lat' => 'required|numeric',
			'lon' => 'required|numeric',
			'elevation' => 'required|numeric',
			'country_id' => 'required|exists:countries,id',
			'website' => 'url',
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::back()->withInput();
		}

		if(is_null($airport = Airport::whereIcao(Input::get('icao'))->whereNew(true)->first())) {
			$airport = new Airport;
			$airport->icao = Input::get('icao');
			$airport->name = Input::get('name');
			$airport->new = true;
			$airport->save();
		}

		Diff::compare($airport, Input::all(), function($key, $value, $model) {
			$change = new AirportChange;
			$change->airport_id = $model->id;
			$change->user_id = Auth::id();
			$change->key = $key;
			$change->value = $value;
			$change->save();
		}, ['name', 'iata', 'city', 'country_id', 'lat', 'lon', 'elevation', 'website']);

		Messages::success('Thank you for your submission. We will check whether all information is correct and soon this airport might be available.');
		return Redirect::back();
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

	function edit(Airport $airport) {
		$countries = Country::orderBy('country')->lists('country','id');
		return $this->autoRender(compact('airport','countries'));
	}

	function update(Airport $airport) {
		Diff::compare($airport, Input::all(), function($key, $value, $model) {
			$change = new AirportChange;
			$change->airport_id = $model->id;
			$change->user_id = Auth::id();
			$change->key = $key;
			$change->value = $value;
			$change->save();
		});

		Messages::success('Thank you for your submission. We will be evaluating your feedback soon.');
		return Redirect::route('airport.show', $airport->icao);
	}

}