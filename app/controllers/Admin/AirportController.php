<?php namespace Admin;

use BaseController, Airport, AirportChange, Input, Redirect;

class AirportController extends BaseController {
	
	protected $layout = 'layouts.admin';

	protected $columns = array(
		'icao' => 'ICAO',
		'iata' => 'IATA',
		'name' => 'Name',
		'city' => 'City',
		'country_id' => 'Country',
		'lat' => 'Latitude',
		'lon' => 'Longitude',
		'elevation' => 'Elevation',
	);

	function index() {
		$columns = $this->columns;

		$changes = AirportChange::with('airport')->get();
		$airportChanges = array();
		foreach($changes as $change) {
			if(!in_array($change->airport_id, $airportChanges)) {
				$airportChanges[$change->airport_id]['airport'] = $change->airport;
				$airportChanges[$change->airport_id]['fields'][] = $columns[$change->key]; 
			} elseif(!in_array($change->key, $airportChanges[$change->airport_id]['fields'])) {
				$airportChanges[$change->airport_id]['fields'][] = $columns[$change->key];
			}
		}

		$this->stylesheet('assets/admin/stylesheets/datatables/dataTables.bootstrap.css');
		$this->javascript('assets/admin/javascript/plugins/datatables/jquery.dataTables.js');
		$this->javascript('assets/admin/javascript/plugins/datatables/dataTables.bootstrap.js');
		$this->autoRender(compact('airportChanges','columns'), 'Airports');
	}

	function requests(Airport $airport) {
		$raw = AirportChange::whereAirportId($airport->id)->with('user')->get();

		$columns = $this->columns;

		$changes = array_fill_keys(array_keys($columns), array('Current' => array(), 'Requests' => array()));
		$hasChange = false;

		foreach($raw as $change) {
			if(!in_array($change->value, $changes[$change->key]['Requests'])) {
				if(count($changes[$change->key]['Requests']) == 0) {
					$changes[$change->key]['Current'][-1] = 'Do nothing - "' . $airport->getOriginal($change->key) . '"';
					$changes[$change->key]['Current'][0] = 'Discard requests';
				}

				$hasChange = true;
				$changes[$change->key]['Requests'][$change->id] = '"' . $change->value . '" by ' . $change->user->name . ' (' . $change->user_id . ')';
			}
		}

		$this->autoRender(compact('airport','changes','columns','hasChange'));
	}

	function change(Airport $airport) {
		$columns = array_keys($this->columns);

		$name = $airpor->icao . ' - ' . $airport->name;

		foreach($columns as $column) {
			if(Input::has($column)) {
				if(Input::get($column) > 0)
					$airport->{$column} = AirportChange::find(Input::get($column))->value;

				if(Input::get($column) >= 0)
					// Delete all entries related to this column
					AirportChange::whereKey($column)->whereAirportId($airport->id)->delete();
			} 
		}

		if(count($airport->getDirty()) > 0) {
			$dirty = array();
			foreach($airport->getDirty() as $field => $value) {
				$dirty[$this->columns[$field]] = array($airport->getOriginal($field), $value);
			}

			$timeline = new Timeline;
			$timeline->type = 'airport-change';
			$timeline->user_id = Auth::id();
			$timeline->activity = array(
				'airport' => $name,
				'fields' => $dirty
			);
			$timeline->save();
		}

		$airport->save();

		return Redirect::route('admin.airport.requests', $airport->icao);
	}

}