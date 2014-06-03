<?php namespace Admin;

use BaseController, Jira, JiraIssue, Auth, Messages, Redirect;

class IssueController extends BaseController {
	
	protected $layout = 'layouts.admin';

	function index() {

	}

	function progress() {
		$priorities = JiraIssue::where('status', '3')->orderBy('priority','desc')->orderBy('updatedDate', 'DESC')->get()->groupBy('priority_id');
		
		$colours = array();
		foreach(Jira::caller('priority') as $colour) {
			$colours[$colour->id] = $colour;
		}

		$this->autoRender(compact('priorities','colours'), 'Issues In Progress');
	}

	function assignedToMe() {
		if(is_null($team = Auth::user()->team)) {
			return;
		} elseif(is_null($team->jira)) {
			Messages::warning('Please enter your JIRA username.');
			return Redirect::route('admin.team.show', $team->id);
		}

		$priorities = JiraIssue::where('assignee',$team->jira)->where('resolution','Unresolved')->orderBy('priority','desc')->orderBy('updatedDate', 'DESC')->get()->groupBy('priority_id');
		
		$colours = array();
		foreach(Jira::caller('priority') as $colour) {
			$colours[$colour->id] = $colour;
		}

		// echo '<pre>';
		// dd($priorities);

		$this->render('admin.issue.progress', compact('priorities','colours'), 'Assigned to Me');
	}

}