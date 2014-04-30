<?php

class AirlineController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$airlines = Airline::orderBy('name')->paginate(50);

		$this->autoRender(compact('airlines'), 'Airlines');
	}

	function show(Airline $airline) {
		$activeFlights = $airline->flights()->whereIn('state',[0, 1, 3, 4])->with('pilot','departure','arrival')->orderBy('departure_time','desc')->get();
		$flights = $airline->flights()->whereState(2)->with('pilot','departure','arrival')->orderBy('departure_time','desc')->get();
		
		$totalDuration = $flights->sum('duration');

		$pilots = $airline->flights()->whereState(2)->leftJoin('pilots','flights.vatsim_id','=','pilots.vatsim_id')->select('pilots.*', DB::raw('SUM(flights.duration) AS duration'))->orderBy('duration','desc')->groupBy('flights.vatsim_id')->take(5)->get()
			->transform(function($pilot) use ($totalDuration) {
				return array('name' => $pilot->name, 'duration' => $pilot->duration, 'percent' => number_format($pilot->duration/$totalDuration * 100, 1));
			});
		$pilots->add(array('name' => 'Others', 'duration' => ($totalDuration - $pilots->sum('duration')), 'percent' => number_format(($totalDuration - $pilots->sum('duration'))/$totalDuration * 100, 1)));
		$pilots = $pilots->toArray();
		foreach($pilots as &$pilot) {
			$pilot = array($pilot['name'], $pilot['duration']);
		}

		$pilots = piechartData($pilots);

		$aircraft = $airline->flights()->whereState(2)->with('aircraft')->whereNotNull('aircraft_id')->where('aircraft_id','!=','')->select('aircraft_id', DB::raw('SUM(duration) AS duration'))->orderBy('duration','desc')->groupBy('aircraft_id')->take(5)->get()
			->transform(function($aircraft) use ($totalDuration) {
				return array('name' => $aircraft->aircraft->implode('name','<br />'), 'duration' => $aircraft->duration, 'percent' => number_format($aircraft->duration/$totalDuration * 100, 1));
			});

		$aircraft->add(array('name' => 'Other', 'duration' => ($totalDuration - $aircraft->sum('duration')), 'percent' => number_format(($totalDuration - $aircraft->sum('duration'))/$totalDuration * 100, 1)));
		$aircraft = $aircraft->toArray();
		foreach($aircraft as &$airplane) {
			$airplane = array($airplane['name'], $airplane['duration']);
		}

		$aircraft = piechartData($aircraft);

		$flights = $flights->take(25);

		$this->javascript('assets/javascript/jquery.flot.min.js');
		$this->javascript('assets/javascript/jquery.flot.pie.min.js');
		$this->autoRender(compact('airline','flights','pilots','aircraft','activeFlights'), $airline->icao . ' - ' . $airline->name);
	}

}