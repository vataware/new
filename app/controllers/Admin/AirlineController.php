<?php namespace Admin;

use BaseController, Airline, AirlineChange, Input, Redirect, Timeline, Auth;

class AirlineController extends BaseController {
	
	protected $layout = 'layouts.admin';

	protected $columns = array(
		'icao' => 'ICAO',
		'name' => 'Name',
		'radio' => 'Callsign',
		'website' => 'Website',
	);

	function index() {
		$columns = $this->columns;

		$changes = AirlineChange::with('airline')->get();
		$airlineChanges = array();
		foreach($changes as $change) {
			if(!in_array($change->airline_id, $airlineChanges)) {
				$airlineChanges[$change->airline_id]['airline'] = $change->airline;
				$airlineChanges[$change->airline_id]['fields'][] = $columns[$change->key]; 
			} elseif(!in_array($change->key, $airlineChanges[$change->airline_id]['fields'])) {
				$airlineChanges[$change->airline_id]['fields'][] = $columns[$change->key];
			}
		}

		$this->stylesheet('assets/admin/stylesheets/datatables/dataTables.bootstrap.css');
		$this->javascript('assets/admin/javascript/plugins/datatables/jquery.dataTables.js');
		$this->javascript('assets/admin/javascript/plugins/datatables/dataTables.bootstrap.js');
		$this->autoRender(compact('airlineChanges','columns'), 'Airlines');
	}

	function requests(Airline $airline) {
		$raw = AirlineChange::whereAirlineId($airline->id)->with('user')->get();

		$columns = $this->columns;

		$changes = array_fill_keys(array_keys($columns), array('Current' => array(), 'Requests' => array()));
		$hasChange = false;

		foreach($raw as $change) {
			if(!in_array($change->value, $changes[$change->key]['Requests'])) {
				if(count($changes[$change->key]['Requests']) == 0) {
					$changes[$change->key]['Current'][-1] = 'Do nothing - "' . $airline->getOriginal($change->key) . '"';
					$changes[$change->key]['Current'][0] = 'Discard requests';
				}

				$hasChange = true;
				$changes[$change->key]['Requests'][$change->id] = '"' . $change->value . '" by ' . $change->user->name . ' (' . $change->user_id . ')';
			}
		}

		$this->autoRender(compact('airline','changes','columns','hasChange'), 'Airline Changes');
	}

	function change(Airline $airline) {
		$columns = array_keys($this->columns);

		$name = $airline->icao . ' - ' . $airline->name;

		foreach($columns as $column) {
			if(Input::has($column)) {
				if(Input::get($column) > 0)
					$airline->{$column} = AirlineChange::find(Input::get($column))->value;

				if(Input::get($column) >= 0)
					// Delete all entries related to this column
					AirlineChange::whereKey($column)->whereAirlineId($airline->id)->delete();
			} 
		}

		if(count($airline->getDirty()) > 0) {
			$dirty = $airport->getDirty();
			foreach($dirty as $field => &$value) {
				$value = array($airline->getOriginal($field), $value);
			}

			$timeline = new Timeline;
			$timeline->type = 'airline-change';
			$timeline->user_id = Auth::id();
			$timeline->activity = array(
				'airline' => $name,
				'fields' => $dirty
			);
			$timeline->save();
		}

		$airline->save();

		return Redirect::route('admin.airline.requests', $airline->icao);
	}

}