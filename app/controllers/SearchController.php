<?php

class SearchController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {

		$q = Input::get('q');
		if(empty($q) || !Input::has('q')) return Redirect::home();

		$pilots = Pilot::where('vatsim_id','=',$q)->orWhere('name','LIKE','%' . $q . '%')->where('vatsim_id','!=',0)->get();
		$flights = Flight::where('callsign','=',$q)->orderBy('departure_time','desc')->get();

		$this->autoRender(compact('q','flights','pilots'), 'Search');
	}

}