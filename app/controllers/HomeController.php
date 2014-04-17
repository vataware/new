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
		$distance = Cache::get('vatsim.distance');

		$flights = Flight::with('pilot','departure','arrival')->whereState(1)->whereMissing(0)->orderBy(DB::raw('RAND()'))->take(15)->get();

		$this->autoRender(compact('pilots','atc','users','year','month','day','change','changeArrow','distance','flights'));
	}

	function team() {
		$members = Team::orderBy('priority')->orderBy(DB::raw('RAND()'))->get();

		$this->stylesheet('assets/stylesheets/team.css');
		$this->autoRender(compact('members'), 'Team');
	}

}