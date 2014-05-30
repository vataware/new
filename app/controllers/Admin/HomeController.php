<?php namespace Admin;

use BaseController, Timeline, User, AirlineChange, AirportChange;

class HomeController extends BaseController {

	protected $layout = 'layouts.admin';
	
	function index() {
		// $jira = new \Jira\JiraClient('http://tracon.vataware.com:8080');
		// $jira->login('api', '5Eq+T7}K/)N2^Gf');
		// $issueCount = count($jira->issues()->getFromJqlSearch('resolution = Unresolved'));

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

	function bugs() {
		$jira = new \Jira\JiraClient('http://tracon.vataware.com:8080');
		$jira->login('api', '5Eq+T7}K/)N2^Gf');

		$result = 
			//$jira->issue('VAT-1')->get();
			// $jira->user('brett')->get();
			// $jira->issues()->getFromJqlSearch('assignee in (superadmin)');
			$jira->priorities()->get();


		echo '<pre>';
		dd($result);
	}

}