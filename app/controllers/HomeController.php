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
		$change = Cache::get('vatsim.change');
		$changeArrow = Cache::get('vatsim.changeDirection');

		$flights = Flight::whereState(1)->orderBy(DB::raw('RAND()'))->take(15)->get();

		$this->autoRender(compact('pilots','atc','users','year','month','day','change','changeArrow','flights'));
	}

}