<?php

/**
 * This command is to be run every couple of minutes to check
 * if there is an update available from VATSIM and inserts the
 * records into the database as needed.
 *
 * @author Roy De Vos Burchart <dev@bonroyage.com>
 * @since 1.0
 */

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class VatawareUpdateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vataware:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch new Vatsim data';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	protected $updateId;
	protected $updateDate;
	protected $vatsim = null;
	protected $airports = null;
	protected $airlines = null;
	protected $registrations = null;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		Log::info('vataware:update - start script');
		$vatsim = $this->loadVatsim();
		Log::info('vataware:update - fetched remote data');
		$general = $vatsim->getGeneralInfo()->toArray();
		$this->updateDate = $updateDate = Carbon::createFromTimestampUTC($general['update']);

		Cache::forever('vatsim.pilots', $vatsim->getPilots()->count());
		Cache::forever('vatsim.atc', $vatsim->getControllers()->count());
		Cache::forever('vatsim.users', $vatsim->getPilots()->count() + $vatsim->getControllers()->count());

		if(!is_null(Update::whereTimestamp($updateDate)->first())) {
			Log::info('vataware:update - terminating execution - data already exists (' . $updateDate . ')');
			return;
		}

		Log::info('vataware:update - importing data from ' . $updateDate);

		$update = new Update;
		$update->timestamp = $updateDate;
		$update->save();

		$this->updateId = $update->id;
		$datas = $this->getVatsimPilots();
		$this->processPilots($datas);

		$thisYear = Flight::where('startdate','LIKE',date('Y') . '%')->count();
		$lastYear = Flight::where('startdate','LIKE',date('Y',strtotime('last year')) . '%')->count();

		Cache::forever('vatsim.year', number_format(Flight::where('startdate','LIKE',date('Y') . '%')->count()));
		Cache::forever('vatsim.month', number_format(Flight::where('startdate','LIKE',date('Y-m') . '%')->count()));
		Cache::forever('vatsim.day', number_format(Flight::where('startdate','=',date('Y-m-d'))->count()));
		Cache::forever('vatsim.distance', number_format(Flight::where('startdate','=',date('Y-m-d'))->sum('distance') * 0.54));

		if($lastYear == 0) {
			Cache::forever('vatsim.change', '&infin;&nbsp;');
			Cache::forever('vatsim.changeDirection', 'up');
		} else {
			$percentageChange = (($thisYear - $lastYear) / $lastYear * 100);
			Cache::forever('vatsim.change', number_format(abs($percentageChange)));
			Cache::forever('vatsim.changeDirection', ($percentageChange > 0) ? 'up' : 'down');
		}
		
		$datas = $this->getVatsimControllers();
		$this->processControllers($datas);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

	function duration($start, $now) {
		return $start->diffInMinutes($now);
	}

	function getAirlines($callsign = null) {
		if(is_null($this->airlines)) $this->airlines = Airline::get();

		if(!is_null($callsign)) 
			return $this->airlines->first(function($key, $airline) use ($callsign) {
				return preg_match('/^' . $airline->icao . '[0-9]{1,5}[A-Z]{0,2}$/', $callsign);
			});

		return $this->airlines;
	}

	function getAirports($icao = null) {
		if(is_null($this->airports)) $this->airports = Airport::lists('country_id','id');

		if(!is_null($icao)) 
			return array_key_exists($icao, $this->airports) ? $this->airports[$icao] : '';

		return $this->airports;
	}

	function getRegistrations($callsign = null) {
		if(is_null($this->registrations)) $this->registrations = Registration::get()->each(function($registration) {
			$registration->prefix = str_replace('-', '', $registration->prefix);
			if(!$registration->regex) $registration->prefix .= '.*';
		});

		if(!is_null($callsign)) 
			return $this->registrations->first(function($key, $registration) use ($callsign) {
				return preg_match('/^' . $registration->prefix . '$/', $callsign);
			});

		return $this->registrations;
	}

	function loadVatsim() {
		if(!is_null($this->vatsim)) return $this->vatsim;

		$vatsim = new Vatsimphp\VatsimData();
		$vatsim->setConfig('forceDataRefresh',true);
		$vatsim->loadData();

		return $this->vatsim = $vatsim;
	}

	function getVatsimPilots() {
		return $this->vatsim->getPilots()->toArray();
	}

	function getVatsimControllers() {
		return $this->vatsim->getControllers()->toArray();
	}

	function positionReport($data, $flightId) {
		$position = new Position;

		$position->flight_id = $flightId;
		
		$position->lat = $data['latitude'];
		$position->lon = $data['longitude'];
		$position->altitude = $data['altitude'];
		$position->speed = $data['groundspeed'];
		$position->heading = $data['heading'];
		// $position->ground_elevation = 
		
		$position->update_id = $this->updateId;
		$position->time = $this->updateDate;

		$position->save();
	}

	function pilot($data, $rating = false) {
		$pilot = Pilot::whereVatsimId($data['cid'])->first();
		if(is_null($pilot)) {
			$pilot = new Pilot;
			$pilot->vatsim_id = $data['cid'];
			$pilot->name = $data['realname'];
			$pilot->rating_id = $data['rating'];
			$pilot->save();
		} elseif($rating === true) {
			$pilot->rating_id = $data['rating'];
			$pilot->save();
		}
	}

	function proximity($latitude, $longitude, $range = 20) {
		return Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $latitude . ")) + cos(radians(`lat`)) * cos(radians(" . $latitude . ")) * cos(radians(`lon`) - radians(" . $longitude . "))) * 6371 AS distance"))
			->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $latitude . ")) + cos(radians(`lat`)) * cos(radians(" . $latitude . ")) * cos(radians(`lon`) - radians(" . $longitude . "))) * 6371 < " . $range)
			->orderBy('distance','asc')
			->first();
	}

	function extractAircraft($code) {
		if(empty($code)) return '';

		preg_match('/(?:.\/)?([^\/]+)(?:\/.)?/', $code, $aircraft);
		return $aircraft[1];
	}

	function altitudeRange($altitude, $base, $range = 20) {
		return ($altitude >= $base - $range && $altitude <= $base + $range);
	}

	function processPilots($data) {
		Log::info('vataware:update - start processing pilots');
		Log::info('vataware:update - found ' . count($data) . ' flights in vatsim data');
		$callsigns = array_pluck($data, 'callsign');
		$callsigns = array_combine($callsigns, $data);
		$updateDate = $this->updateDate;
		$flights = Flight::where('state','!=',2)->with('lastPosition')->get();
		Log::info('vataware:update - found ' . $flights->count() . ' flights in database');
		foreach($flights as $flight) {
			if(is_null($flight->lastPosition)) {
				// remove flight if there's no known last position
				$flight->delete();
			} elseif(!array_key_exists($flight->callsign, $callsigns)) {
			// flight missing
				if(Carbon::now()->diffInHours($flight->lastPosition->updated_at) >= 1) {
					// no record of last position
					$flight->delete();
				} else {
					// flight has been missing for less than an hour
					$flight->missing = true;

					if($flight->isAirborne() || $flight->isArriving()) {
					// airborne or near arrival
						$nearby = $this->proximity($flight->last_lat, $flight->last_lon);
						if(!is_null($nearby) && $this->altitudeRange($flight->lastPosition->altitude, $nearby->elevation) && $flight->lastPosition->groundspeed < 30) {
							// Airport is within range (20km), altitude is within elevation +/- 20ft and ground speed < 30 kts
							$flight->stateArrived();
							$flight->arrival_time = $flight->lastPosition->time;
							$flight->setArrival($nearby);
							$flight->missing = false;
						}
					}

					$flight->save();
				}
			} else {
				$data = $callsigns[$flight->callsign];
				if(is_null($flight->lastPosition)) $this->positionReport($data, $flight->id);

				if($flight->isAirborne())
				{
					$flight->duration = $this->duration($flight->departure_time, $updateDate);

					$nearby = $this->proximity($data['latitude'], $data['longitude']);
					if(!is_null($nearby) && $this->altitudeRange($data['altitude'], $nearby->elevation) && $data['groundspeed'] < 30) {
						// Airport is within range (20km), altitude is within elevation +/- 20ft and ground speed < 30 kts
						$flight->stateArriving();
					}
				}
				elseif($flight->isArriving())
				{
					$flight->duration = $this->duration($flight->departure_time, $updateDate);
					$nearby = $this->proximity($data['latitude'], $data['longitude']);
					if(!is_null($nearby) && $this->altitudeRange($data['altitude'], $nearby->elevation) && $data['groundspeed'] < 30) {
						// Airport is within range (20km), altitude is within elevation +/- 20ft and ground speed < 30 kts
						$flight->stateArrived();
						$flight->arrival_time = $updateDate;
						$flight->setArrival($nearby);
					} else {
						// Make sure flight is marked as Airborne
						$flight->stateAirborne();
					}
				}
				elseif($flight->isPreparing())
				{
					if($data['longitude'] <> $flight->last_lon || $data['latitude'] <> $flight->last_lat || !$this->altitudeRange($data['altitude'], $flight->lastPosition->altitude, 10)) {
						// Plane has moved (horizontally or vertically)
						$flight->stateDeparting();
					}
				}

				if($flight->isDeparting() &&
					$data['altitude'] >= 
					($flight->lastPosition->altitude + 50)) {
					// Flight is departing and altitude has increased by more than 50 ft
					$flight->stateAirborne();
					$flight->departure_time = $updateDate;
					$flight->arrival_time = Carbon::instance($updateDate)->addHours($data['planned_hrsenroute'])->addMinutes($data['planned_minenroute']);
				}

				if(empty($flight->departure_id)) {
					$flight->departure_id = $data['planned_depairport'];
					$flight->departure_id = $this->getAirports($data['planned_depairport']);
				}

				if(empty($flight->arrival_id)) {
					$flight->arrival_id = $data['planned_destairport'];
					$flight->arrival_country_id = $this->getAirports($data['planned_destairport']);
				}

				$flight->route = $data['planned_route'];
				$flight->remarks = $data['planned_remarks'];
				$flight->flighttype = $data['planned_flighttype'];

				// Aircraft codes
				$flight->aircraft_code = $data['planned_aircraft'];
				$flight->aircraft_id = $this->extractAircraft($data['planned_aircraft']);
				
				// Constantly update filed altitude, speed from flight plan
				$flight->altitude = $data['planned_altitude'];
				$flight->speed = $data['planned_tascruise'];

				// Update distance flown
				if($flight->last_lat != 0 && $flight->last_lon != 0) $flight->distance += acos(sin(deg2rad($flight->last_lat)) * sin(deg2rad($data['latitude'])) + cos(deg2rad($flight->last_lat)) * cos(deg2rad($data['latitude'])) * cos(deg2rad($flight->last_lon) - deg2rad($data['longitude']))) * 6371;
				
				// Update latest coordinates
				$flight->last_lat = $data['latitude'];
				$flight->last_lon = $data['longitude'];

				// Ensure flight is not marked as missing
				$flight->missing = false;
				
				$flight->save();

				// Create position report
				$this->positionReport($data, $flight->id);
			}
			unset($callsigns[$flight->callsign]);
		}
		Log::info('vataware:update - done processing existing flights');
		Log::info('vataware:update - adding ' . count($callsigns) . ' flights to the database');
		foreach($callsigns as $data) {
			/* Skip flight if there are no coordinates */
			if(empty($data['longitude']) || empty($data['latitude'])) continue;

			$date = Carbon::createFromFormat('YmdHis', $data['time_logon'], 'UTC');
			$flight = new Flight;
			
			// Create entry for Pilot
			$this->pilot($data);

			$flight->vatsim_id = $data['cid'];
			$flight->startdate = $date->toDateString();

			// If no departure airport is defined, check for airport in proximity.
			if(empty($data['planned_depairport']))
			{
				$nearby = $this->proximity($data['latitude'], $data['longitude']);
				if(!is_null($nearby)) $flight->setDeparture($nearby);
			}
			else
			{
				$flight->departure_id = $data['planned_depairport'];
				$flight->departure_country_id = $this->getAirports($data['planned_depairport']);
			}

			// Arrival airport
			$flight->arrival_id = $data['planned_destairport'];
			$flight->arrival_country_id = $this->getAirports($data['planned_destairport']);
			
			// Callsign, airline/private registration
			$flight->callsign = $data['callsign'];
			$callsign = str_replace('-','',strtoupper($data['callsign']));
			if(!is_null($airline = $this->getAirlines($callsign))) { // Airline
				$flight->isAirline($airline->icao);
			} elseif(!is_null($registration = $this->getRegistrations($callsign))) {
				$flight->isPrivate($registration->country_id);
			}

			// Set status as 'Preparing'
			$flight->statePreparing();
			$flight->save();

			$this->positionReport($data, $flight->id);
		}

		Log::info('vataware:update - finished processing pilots');
	}

	function processControllers($data) {
		Log::info('vataware:update - start proocessing controllers');
		Log::info('vataware:update - found ' . count($data) . ' controllers in vatsim data');
		$updateDate = $this->updateDate;
		$callsigns = array_pluck($data, 'callsign');
		$callsigns = array_combine($callsigns, $data);
		$controllers = ATC::whereNull('end')->with('pilot')->get();
		Log::info('vataware:update - found ' . $controllers->count() . ' controllers in database');
		foreach($controllers as $controller) {
			if(!array_key_exists($controller->callsign, $callsigns)) {
			// controller missing
				if(Carbon::now()->diffInMinutes($controller->time) >= 5) {
					// no record of last position
					$controller->end = $controller->time;
				} else {
					// controller has been missing for less than an hour
					$controller->missing = true;
				}
				$controller->save();
			} else {
				$data = $callsigns[$controller->callsign];

				$controller->pilot->rating_id = $data['rating'];
				$controller->pilot->save();

				$controller->duration = $this->duration($controller->start, $updateDate);
				
				$controller->missing = false;
				$controller->time = $updateDate;
				$controller->save();
			}
			unset($callsigns[$controller->callsign]);
		}
		Log::info('vataware:update - done processing existing controllers');
		Log::info('vataware:update - adding ' . count($callsigns) . ' controllers to the database');
		foreach($callsigns as $data) {
			$controller = new ATC;
			$controller->vatsim_id = $data['cid'];
			$controller->callsign = $data['callsign'];
			$controller->start = Carbon::createFromFormat('YmdHis', $data['time_logon']);
			$controller->facility_id = (ends_with($data['callsign'], '_ATIS')) ? 99 : $data['facilitytype'];
			$controller->rating_id = $data['rating'];
			$controller->visual_range = $data['visualrange'];
			$controller->lat = $data['latitude'];
			$controller->lon = $data['longitude'];
			$controller->frequency = $data['frequency'];
			$controller->facility_id = (ends_with($data['callsign'], '_ATIS')) ? 99 : $data['facilitytype'];

			if($controller->facility_id < 6) {
				$nearby = $this->proximity($data['latitude'], $data['longitude']);
				$controller->airport_id = (is_null($nearby)) ? null : $nearby->id;
			}

			$this->pilot($data, true);
			
			$controller->missing = false;
			$controller->time = $updateDate;
			$controller->save();
		}
		Log::info('vataware:update - finished processing controllers');
	}

}
