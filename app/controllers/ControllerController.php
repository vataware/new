<?php

class ControllerController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$pilots = Flight::with('pilot')->select(DB::raw('vatsim_id, COUNT(vatsim_id) AS aggregate, SUM(distance) as distance, SUM(duration) as duration'))->orderBy('aggregate','desc')->groupBy('vatsim_id')->paginate(50);

		$this->autoRender(compact('pilots'),'Pilots');
	}

	function show(Pilot $pilot) {
		$actives = ATC::with('airport','airport.country')->whereVatsimId($pilot->vatsim_id)->whereNull('end')->where('facility_id','!=',99)->get();
		$duties = ATC::with('airport','airport.country')->whereVatsimId($pilot->vatsim_id)->whereNotNull('end')->where('facility_id','!=',99)->orderBy('end','desc')->take(15)->get();

		if($pilot->processing == 0) {
			Queue::push('LegacyUpdate', $pilot->vatsim_id, 'legacy');
			$pilot->processing = 2;
			$pilot->save();
		}

		if($pilot->processing == 2) {
			Messages::success('The data for this controller is currently being processed. In a couple of minutes, all statistics will be available.')->one();
		}

		$stat = new ControllerStat(ATC::whereVatsimId($pilot->vatsim_id)->where('facility_id','!=',99));
		extract($stat->durations($pilot->duration_atc));
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