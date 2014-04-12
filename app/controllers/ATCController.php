<?php

class ATCController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$atc = ATC::with('pilot','airport','airport.country')->orderBy('callsign')->whereNull('end')->paginate(25);
		
		$this->autoRender(compact('atc'), 'ATC');
	}

	function show(ATC $controller) {
		if(!is_null($controller->airport_id) && $controller->facility_id >= 2 && $controller->facility_id <= 5) {
			$otherControllers = ATC::whereAirportId($controller->airport_id)->whereNull('end')->where('facility_id','<', $controller->facility_id)->orderBy('facility_id','desc')->pluck('facility_id');
			$otherControllers = range(max(2, $otherControllers+1), $controller->facility_id);
		} else {
			$otherControllers = array();
		}

		$this->autoRender(compact('controller','otherControllers'), $controller->callsign);
	}

}