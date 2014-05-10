<?php

class HomeController extends BaseController {

	protected $layout = 'layouts.master';

	public function index() {
		$users = DbConfig::get('vatsim.users');
		$year = DbConfig::get('vatsim.year');
		$month = DbConfig::get('vatsim.month');
		$day = DbConfig::get('vatsim.day');
		$change = DbConfig::get('vatsim.change');
		$changeArrow = DbConfig::get('vatsim.changeDirection');
		$distance = DbConfig::get('vatsim.distance');

		$flights = Flight::with('pilot','departure','arrival')->whereState(1)->whereMissing(0)->orderBy(DB::raw('RAND()'))->take(15)->get();

		$this->autoRender(compact('users','year','month','day','change','changeArrow','distance','flights'));
	}

	function team() {
		$members = Team::orderBy('priority')->orderBy(DB::raw('RAND()'))->get();

		$this->stylesheet('assets/stylesheets/team.css');
		$this->autoRender(compact('members'), 'Team');
	}

	function donations() {
		$donations = Donation::orderBy('amount')->remember(1440)->lists('name');

		$this->stylesheet('assets/stylesheets/donations.css');
		$this->autoRender(compact('donations'), 'Donations');
	}

	function mapApi() {
		$zoom = max(2, min(14, Input::get('z')));
		$lat = Input::has('lat') ? max(-90, min(90, Input::get('lat'))) : 30;
		$lon = Input::has('lon') ? max(-180, min(180, Input::get('lon'))) : 0;

		Session::put('map.zoom', $zoom);
		Session::put('map.coordinates', $lat . ',' . $lon);

		if(Input::get('force') != '1' && DbConfig::has('vatsim.nextupdate') && Carbon::now()->lt(DbConfig::get('vatsim.nextupdate')))
			return [];

		return DbConfig::get('vatsim.map');
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