<?php namespace Admin;

use BaseController, Timeline, User, AirlineChange, AirportChange, JiraIssue;

class HomeController extends BaseController {

	protected $layout = 'layouts.admin';
	
	function index() {
		$issueCount = count(JiraIssue::where('resolution','Unresolved')->get());

		$userCount = User::where('last_login','!=','0000-00-00 00:00:00')->remember(1)->count();
		$editRequest = count(AirlineChange::groupBy('airline_id')->remember(1)->lists('airline_id')) + count(AirportChange::groupBy('airport_id')->remember(1)->lists('airport_id'));

		$this->autoRender(compact('issueCount','userCount','editRequest'), 'Dashboard');
	}

	function activity() {
		$timelines = Timeline::with('user')->orderBy('created_at','desc')->get()->groupBy(function($timeline) {
			return $timeline->created_at->format('j M. Y');
		});

		$this->autoRender(compact('timelines'), 'Timeline');
	}

}