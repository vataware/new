<?php

class HomeController extends BaseController {

	protected $layout = 'layouts.master';

	public function index() {
		$pilots = Cache::get('vatsim.pilots');
		$atc = Cache::get('vatsim.atc');
		$users = Cache::get('vatsim.users');
		$year = Cache::get('vatsim.year');
		$month = Cache::get('vatsim.month');
		$day = Cache::get('vatsim.day');
		$change = Cache::get('vatsim.change');
		$changeArrow = Cache::get('vatsim.changeDirection');
		$distance = Cache::get('vatsim.distance');

		$flights = Flight::with('pilot','departure','arrival')->whereState(1)->whereMissing(0)->orderBy(DB::raw('RAND()'))->take(15)->get();

		$this->javascript('http://jqueryrotate.googlecode.com/svn/trunk/jQueryRotate.js');
		$this->stylesheet('assets/stylesheets/map-rotate.css');
		$this->autoRender(compact('pilots','atc','users','year','month','day','change','changeArrow','distance','flights'));
	}

	function team() {
		$members = Team::orderBy('priority')->orderBy(DB::raw('RAND()'))->get();

		$this->stylesheet('assets/stylesheets/team.css');
		$this->autoRender(compact('members'), 'Team');
	}

	function mapApi() {
		$zoom = max(2, min(14, Input::get('z')));
		$lat = Input::has('lat') ? max(-90, min(90, Input::get('lat'))) : 30;
		$lon = Input::has('lon') ? max(-180, min(180, Input::get('lon'))) : 0;

		Session::put('map.zoom', $zoom);
		Session::put('map.coordinates', $lat . ',' . $lon);

		if(Input::get('force') != '1' && Cache::has('vatsim.nextupdate') && Carbon::now()->lt(Cache::get('vatsim.nextupdate')))
			return [];

		return Cache::get('vatsim.map');
	}

	function mapFlight() {
		if(!Input::has('id'))
			return App::abort(400, 'Missing data');

		$flight = Flight::find(Input::get('id'));
		
		if(is_null($flight))
			return App::abort(404);

		$positions = array_map(function($position) {
			return array($position['lat'], $position['lon']);
		}, $flight->positions->toArray());

		return array('colours' => $flight->mapsColours, 'coordinates' => $positions);
	}

}