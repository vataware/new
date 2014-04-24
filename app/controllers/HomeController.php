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

		$this->autoRender(compact('pilots','atc','users','year','month','day','change','changeArrow','distance','flights'));
	}

	function team() {
		$members = Team::orderBy('priority')->orderBy(DB::raw('RAND()'))->get();

		$this->stylesheet('assets/stylesheets/team.css');
		$this->autoRender(compact('members'), 'Team');
	}

	function mapApi() {
		if(!Input::has('n') || !Input::has('e') || !Input::has('s') || !Input::has('w'))
			return App::abort(400, 'Missing data');

		$north = Input::get('n');
		$east = Input::get('e');
		$south = Input::get('s');
		$west = Input::get('w');

		$zoom = max(2, min(14, Input::get('z')));
		$lat = Input::has('lat') ? max(-90, min(90, Input::get('lat'))) : 30;
		$lon = Input::has('lon') ? max(-180, min(180, Input::get('lon'))) : 0;

		Session::put('map.zoom', $zoom);
		Session::put('map.coordinates', $lat . ',' . $lon);

		$flights = Flight::with('lastPosition')
			->whereIn('state',[1, 3])
			->where('last_lat', '<=', $north)
			->where('last_lat', '>=', $south)
			->where('last_lon', '<=', $east)
			->where('last_lon', '>=', $west)
			->join('pilots','flights.vatsim_id','=','pilots.vatsim_id')
			->select('flights.*','pilots.name')
			->get()
			->transform(function($flight) {
				return [
					'id' => $flight->id,
					'callsign' => $flight->callsign,
					'vatsim_id' => $flight->vatsim_id,
					'pilot' => $flight->name,
					
					// Terminalds
					'departure' => $flight->departure_id,
					'arrival' => $flight->arrival_id,

					// Aircraft
					'aircraft_code' => $flight->aircraft_code,
					'aircraft_id' => $flight->aircraft_id,

					// Location
					'lat' => $flight->last_lat,
					'lon' => $flight->last_lon,

					// Movement
					'altitude' => $flight->lastPosition->altitude,
					'speed' => $flight->lastPosition->speed,
					'heading' => $flight->lastPosition->heading,
					'icon' => asset('assets/images/mapicons/' . $flight->lastPosition->heading . '.png'),
				];
			});

		return $flights;
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