<?php

class SearchController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {

		$q = Input::get('q');

		$pilots = Pilot::where('vatsim_id','=',$q)->orWhere('name','LIKE','%' . $q . '%')->get();
		$flights = Flight::where('callsign','=',$q)->orderBy('departure_time','desc')->get();

		$this->autoRender(compact('q','flights','pilots'), 'Search');
	}

}