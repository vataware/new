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

		if($flight->pilot->getOriginal('updated_at') == '0000-00-00 00:00:00') {
			Queue::push('LegacyUpdate', $flight->pilot->vatsim_id, 'legacy');
			$flight->pilot->processing = 2;
			$flight->pilot->save();
		}

		$flight->miles = $flight->distance * 0.54;

		$this->autoRender(compact('flight'), $flight->callsign);
	}

	function citypair($departureId, $arrivalId) {
		$routes = Flight::select('id','route', DB::raw('COUNT(route) AS count'))->whereDepartureId($departureId)->whereArrivalId($arrivalId)->where('route','!=','')->whereState(2)->groupBy('route')->orderBy('count','desc')->get();
		
		$departure = Airport::find($departureId);
		$arrival = Airport::find($arrivalId);

		$this->autoRender(compact('departure','arrival','routes','airports','departureId','arrivalId'), 'Routes for ' . $departureId . ' - ' . $arrivalId);
	}

}