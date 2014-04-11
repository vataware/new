<?php

class FlightController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {
		$flights = Flight::with('departureCountry','arrivalCountry','departure','arrival','pilot')->where('departure_time','!=','')->whereState(1)->orderBy('departure_time','desc')->orderBy('callsign')->paginate(25);
		
		$this->autoRender(compact('flights'), 'Flights');
	}

	function show(Flight $flight) {
		if($flight->missing) {
			Messages::error('This flight has been missing for ' . Carbon::now()->diffInMinutes($flight->positions->last()->updated_at) . ' minutes. It will be deleted if it has been missing for 1 hour.')->one();
		}

		$flight->miles = $flight->distance * 0.54;

		$this->autoRender(compact('flight'), $flight->callsign);
	}

}