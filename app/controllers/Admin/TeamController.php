<?php namespace Admin;

use BaseController, Team, Timeline, Input, Auth, Redirect;

class TeamController extends BaseController {
	
	protected $layout = 'layouts.admin';

	function index() {
		$teams = Team::orderBy('name')->get();

		$this->autoRender(compact('teams'), 'Team');
	}

	function show(Team $user) {
		$timelines = Timeline::whereUserId($user->vatsim_id)->orderBy('created_at','desc')->take(5)->get()->groupBy(function($timeline) {
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

	function timeline(Team $user) {
		$timelines = Timeline::whereUserId($user->vatsim_id)->orderBy('created_at','desc')->get()->groupBy(function($timeline) {
			return $timeline->created_at->format('j M. Y');
		});

		$this->autoRender(compact('user','timelines'), $user->firstname . '\'s Activity');
	}

	function update(Team $user) {
		$name = $user->name;

		$rules = array(
			'name' => 'required',
			'job' => 'required',
			'priority' => 'required|integer',
			'firstname' => 'required',
		);

		$user->name = Input::get('name');
		$user->firstname = Input::get('firstname');
		$user->description = Input::get('description');
		$user->job = Input::get('job');
		$user->priority = Input::get('priority');

		$dirty = $user->getDirty();
		foreach($dirty as $field => &$value) {
			$value = array($user->getOriginal($field), $value);
		}

		$timeline = new Timeline;
		$timeline->type = 'user-change';
		$timeline->user_id = Auth::id();
		$timeline->activity = array(
			'user' => $name,
			'fields' => $dirty
		);
		$timeline->save();

		$user->save();

		return Redirect::route('admin.team.show', $user->id);
	}

	function social(Team $user) {
		$rules = array(
			'vatsim' => 'required|integer',
			'facebook' => 'alpha_dash',
			'twitter' => 'alpha_dash',
			'email' => 'email',
			'jira' => 'alpha_dash',
		);

		$user->vatsim_id = Input::get('vatsim');
		$user->facebook = Input::get('facebook');
		$user->twitter = Input::get('twitter');
		$user->email = Input::get('email');
		$user->jira = Input::get('jira') ?: null;

		$dirty = $user->getDirty();
		foreach($dirty as $field => &$value) {
			$value = array($user->getOriginal($field), $value);
		}

		$timeline = new Timeline;
		$timeline->type = 'social-change';
		$timeline->user_id = Auth::id();
		$timeline->activity = array(
			'user' => $user->name,
			'fields' => $dirty
		);
		$timeline->save();

		$user->save();

		return Redirect::route('admin.team.show', $user->id);
	}

	function destroy(Team $user) {

	}

}