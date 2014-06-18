<?php

class SearchController extends BaseController {
	
	protected $layout = 'layouts.master';

	function index() {

		$q = trim(Input::get('q'));
		if(empty($q) || !Input::has('q')) return Redirect::home();

		if(!Input::has('guess') || Input::get('guess') != 'no') {
			$regex = array(
				'pilot' => '[0-9]+',
				'airport' => '[A-Z0-9]{4}',
				'airline' => '[A-Z0-9]{3}',
				'airportIata' => '[A-Z0-9]{3}',
				'citypair' => '([A-Z0-9]{3,4})(?:(?:\s*?[-|>]\s*?)|\s+to\s+|\s+)([A-Z0-9]{3,4})',
				'callsign' => '.*'
			);

			$search = new Search($q);

			foreach($regex as $type => $pattern) {
				if(preg_match('/^' . $pattern . '$/i', $q, $matches) && ($match = $search->quick($type, $matches))) {
					Messages::info('You were redirected here by a best guess of the search system. <a href="' . URL::route('search', array('q' => $q, 'guess' => 'no')) . '" class="alert-link">Return to search results.</a>');
					return $match;
				}
			}
		}

		$pilots = Pilot::where(function($search) use ($q) {
			$search->where('vatsim_id','=',$q);
			$search->orWhere(function($name) use ($q) {
				$name->where('name','LIKE','%' . $q . '%');
				$name->where('anonymous','=',false);
			});
		})->where('vatsim_id','!=',0)->get();
		$flights = Flight::where('callsign','=',$q)->orderBy('departure_time','desc')->get();
		$airlines = Airline::where('icao','=',$q)->orWhere('name','LIKE','%' . $q . '%')->get();
		$airports = Airport::where('icao','=',$q)->orWhere('iata','=',$q)->orWhere('name','LIKE','%' . $q . '%')->orWhere('city','LIKE','%' . $q . '%')->get();

		$this->autoRender(compact('q','flights','pilots','airlines','airports'), 'Search');
	}

}