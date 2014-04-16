<?php

class ControllerController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$pilots = Flight::with('pilot')->select(DB::raw('vatsim_id, COUNT(vatsim_id) AS aggregate, SUM(distance) as distance, SUM(duration) as duration'))->orderBy('aggregate','desc')->groupBy('vatsim_id')->paginate(50);

		$this->autoRender(compact('pilots'),'Pilots');
	}

	function show(Pilot $pilot) {
		$actives = ATC::with('airport','airport.country')->whereVatsimId($pilot->vatsim_id)->whereNull('end')->where('facility_id','!=',99)->get();
		$duties = ATC::with('airport','airport.country')->whereVatsimId($pilot->vatsim_id)->whereNotNull('end')->where('facility_id','!=',99)->take(15)->get();

		$stat = new ControllerStat(ATC::whereVatsimId($pilot->vatsim_id)->where('facility_id','!=',99));
		extract($stat->durations());
		$airports = $stat->topAirports();
		$facilities = $stat->topFacilities();

		$this->javascript('assets/javascript/jquery.flot.min.js');
		$this->javascript('assets/javascript/jquery.flot.pie.min.js');
		$this->autoRender(compact('pilot', 'duties', 'actives', 'airport', 'airports', 'longest', 'hours', 'minutes', 'facilities'), $pilot->name);
	}

	function flights(Pilot $pilot) {
		$flights = Flight::whereVatsimId($pilot->vatsim_id)->with('departureCountry','arrivalCountry','departure','arrival','pilot')->where('departure_time','!=','')->orderBy('startdate','desc')->orderBy('departure_time','desc')->orderBy('callsign')->paginate(50);
		$this->autoRender(compact('flights','pilot'), 'Flights');
	}

}