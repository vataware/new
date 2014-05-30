<?php namespace Admin;

use BaseController, Team, Timeline;

class TeamController extends BaseController {
	
	protected $layout = 'layouts.admin';

	function index() {
		$teams = Team::orderBy('name')->get();

		$this->autoRender(compact('teams'), 'Team');
	}

	function show(Team $user) {
		$timelines = Timeline::whereUserId($user->vatsim_id)->orderBy('created_at','desc')->get()->groupBy(function($timeline) {
			return $timeline->created_at->format('j M. Y');
		});

		if(!is_null($user->jira)) {
			$jira = new \Jira\JiraClient('http://tracon.vataware.com:8080');
			$jira->login('api', '5Eq+T7}K/)N2^Gf');
			$issues = $jira->issues()->getFromJqlSearch('assignee=' . $user->jira . ' AND resolution = Unresolved ORDER BY updatedDate DESC');
			if(count($issues) > 0) {
				$types = $jira->issueTypes()->get();
				$priorities = $jira->priorities()->get();
			}
		} else {
			$issues = false;
		}

		$this->autoRender(compact('user','timelines','issues','types','priorities'), $user->name);
	}

}