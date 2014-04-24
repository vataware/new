<?php

class ATCController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$facilities =  [
			0 => '- All -',
			1 => 'Flight Service Station',
			2 => 'Clearance Delivery',
			3 => 'Ground',
			4 => 'Tower',
			5 => 'Approach/Departure',
			6 => 'Center'
		];

		$atc = ATC::with('pilot','airport','airport.country','sector')->orderBy('callsign')->whereNull('end')->whereNotIn('facility_id', array(0, 99));
		if(Input::has('facility') && array_key_exists(Input::get('facility'), $facilities) && Input::get('facility') != 0) $atc = $atc->whereFacilityId(Input::get('facility'));
		$atc = $atc->paginate(25);

		// $countries = ATC::with('airport','airport.country')->whereNull('end')->whereNotIn('facility_id', array(0, 99))->whereNotNull('airport_id')->get();
		// $countries = $countries->transform(function($value) {
		// 	return $value->airport->country;
		// })->sortBy('country')->lists('country','id');

		// array_unshift($countries, '- All -');

		$this->autoRender(compact('atc','countries','facilities'), 'ATC');
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