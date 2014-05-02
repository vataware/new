<?php

class PilotController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$pilots = Pilot::orderBy('counter','desc')->take(50)->paginate(50);

		$this->autoRender(compact('pilots'),'Pilots');
	}

	function show(Pilot $pilot) {
		$active = Flight::with('departure','departure.country','arrival','arrival.country')->whereVatsimId($pilot->vatsim_id)->whereIn('state',array(0,1,3,4))->first();
		$flights = Flight::with('departure','departure.country','arrival','arrival.country')->whereVatsimId($pilot->vatsim_id)->whereState(2)->orderBy('arrival_time','desc')->take(15)->get();
		$flightCount = Flight::whereVatsimId($pilot->vatsim_id)->whereState(2)->count();

		$stats = new FlightStat(Flight::whereVatsimId($pilot->vatsim_id));

		if($pilot->processing == 0) {
			Queue::push('LegacyUpdate', $pilot->vatsim_id, 'legacy');
			$pilot->processing = 2;
			$pilot->save();
		}

		if($pilot->processing == 2) {
			Messages::success('The data for this pilot is currently being processed. In a couple of minutes, all statistics will be available.')->one();
		}
			
		$distances = $stats->distances($pilot->distance);
		$citypair = $stats->citypair();

		if($flights->count() > 0) {
			$durations = $stats->durations($pilot->duration);
			extract($durations);
		}

		// Charts: popular airlines, airports and aircraft		
		$airlines = $stats->topAirlines();
		$airports = $stats->topAirports();
		$aircraft = $stats->topAircraft();

		$this->javascript('assets/javascript/jquery.flot.min.js');
		$this->javascript('assets/javascript/jquery.flot.pie.min.js');
		$this->autoRender(compact('pilot', 'flights', 'active', 'distances', 'airlines', 'aircraft', 'airports', 'longest', 'shortest', 'citypair', 'hours', 'minutes'), $pilot->name);
	}

	function flights(Pilot $pilot) {
		$flights = Flight::whereVatsimId($pilot->vatsim_id)->with('departureCountry','arrivalCountry','departure','arrival','pilot')->where('departure_time','!=','')->orderBy('startdate','desc')->orderBy('departure_time','desc')->orderBy('callsign')->paginate(50);
		$this->autoRender(compact('flights','pilot'), 'Flights');
	}

}