<?php

class PilotController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$pilots = Flight::with('pilot')->select(DB::raw('vatsim_id, COUNT(vatsim_id) AS aggregate, SUM(distance) as distance, SUM(duration) as duration'))->orderBy('aggregate','desc')->groupBy('vatsim_id')->paginate(50);

		$this->autoRender(compact('pilots'),'Pilots');
	}

	function show(Pilot $pilot) {
		$active = Flight::with('departure','departure.country','arrival','arrival.country')->whereVatsimId($pilot->vatsim_id)->whereIn('state',array(0,1,3,4))->first();
		$flights = Flight::with('departure','departure.country','arrival','arrival.country')->whereVatsimId($pilot->vatsim_id)->whereState(2)->orderBy('arrival_time','desc')->get();

		$stats = new FlightStat(Flight::whereVatsimId($pilot->vatsim_id));
			
		$distances = $stats->distances();
		$citypair = $stats->citypair();

		if($flights->count() > 0) {
			$durations = $stats->durations();
			extract($durations);
		}

		// Charts: popular airlines, airports and aircraft		
		$airlines = $stats->topAirlines();
		$airports = $stats->topAirports();
		$aircraft = $stats->topAircraft();

		$this->autoRender(compact('pilot', 'flights', 'active', 'distances', 'airlines', 'aircraft', 'airports', 'longest', 'shortest', 'citypair', 'hours', 'minutes'), $pilot->name);
	}

	function flights(Pilot $pilot) {
		$flights = Flight::whereVatsimId($pilot->vatsim_id)->with('departureCountry','arrivalCountry','departure','arrival','pilot')->where('departure_time','!=','')->orderBy('startdate','desc')->orderBy('departure_time','desc')->orderBy('callsign')->paginate(50);
		$this->autoRender(compact('flights','pilot'), 'Flights');
	}

}