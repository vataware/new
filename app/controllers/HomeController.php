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

		$this->autoRender(compact('pilots','atc','users','year','month','day'));
	}

	public function flights() {
		$flights = Flight::with('departureCountry','arrivalCountry','departure','arrival','pilot')->where('departure_time','!=','')->whereState(1)->orderBy('departure_time','desc')->orderBy('callsign')->paginate(25);
		
		$this->autoRender(compact('flights'), 'Flights');
	}

	public function flight(Flight $flight) {
		if($flight->missing) {
			Messages::error('This flight has been missing for ' . Carbon::now()->diffInMinutes($flight->positions->last()->updated_at) . ' minutes. It will be deleted if it has been missing for 1 hour.')->one();
		}

		$this->autoRender(compact('flight'), $flight->callsign);
	}

}