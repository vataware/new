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
		Log::info('VATSIM UPDATE: START');
		$vatsim = $this->loadVatsim();
		Log::info('VATSIM UPDATE: FETCHED DATA');
		$general = $vatsim->getGeneralInfo()->toArray();
		$this->updateDate = $updateDate = Carbon::createFromTimestampUTC($general['update']);

		Cache::forever('vatsim.pilots', $vatsim->getPilots()->count());
		Cache::forever('vatsim.atc', $vatsim->getControllers()->count());
		Cache::forever('vatsim.users', $vatsim->getPilots()->count() + $vatsim->getControllers()->count());

		if(!is_null(Update::whereTimestamp($updateDate)->first())) {
			Log::info('VATSIM Data not updated: already exists (' . $updateDate . ')');
			$this->info('This update is already in the database. (' . $updateDate . ')');
			return;
		}

		$update = new Update;
		$update->timestamp = $updateDate;
		$update->save();

		$this->updateId = $update->id;
		Log::info('VATSIM UPDATE: START PILOTS');
		$datas = $this->getVatsimPilots();
		foreach($datas as $data)
		{
			/* Skip flight if there are no coordinates */
			if(empty($data['longitude']) || empty($data['latitude'])) continue;

			$date = Carbon::createFromFormat('YmdHis', $data['time_logon'], 'UTC');
			$record = Flight::whereCallsign($data['callsign'])->whereVatsimId($data['cid'])->with('lastPosition')->where('state','!=',2)->first();
			
			if(is_null($record))
			{
				$record = new Flight;
				
				// Create entry for Pilot
				$this->pilot($data);

				$record->vatsim_id = $data['cid'];
				$record->startdate = $date->toDateString();

				// If no departure airport is defined, check for airport in proximity.
				if(empty($data['planned_depairport']))
				{
					$nearby = $this->proximity($data['latitude'], $data['longitude']);
					if(!is_null($nearby)) $record->setDeparture($nearby);
				}
				else
				{
					$record->departure_id = $data['planned_depairport'];
					$record->departure_country_id = $this->getAirports($data['planned_depairport']);
				}

				// Arrival airport
				$record->arrival_id = $data['planned_destairport'];
				$record->arrival_country_id = $this->getAirports($data['planned_destairport']);
				
				// Callsign, airline/private registration
				$record->callsign = $data['callsign'];
				$callsign = str_replace('-','',strtoupper($data['callsign']));
				if(!is_null($airline = $this->getAirlines($callsign))) { // Airline
					$record->isAirline($airline->icao);
				} elseif(!is_null($registration = $this->getRegistrations($callsign))) {
					$record->isPrivate($registration->country_id);
				}

				// Set status as 'Preparing'
				$record->statePreparing();
			}
			elseif($record->isAirborne())
			{
				$record->duration = $this->duration($record->departure_time, $updateDate);

				$nearby = $this->proximity($data['latitude'], $data['longitude']);
				if(!is_null($nearby) && $this->altitudeRange($data['altitude'], $nearby->elevation) && $data['groundspeed'] < 30) {
					// Airport is within range (20km), altitude is within elevation +/- 20ft and ground speed < 30 kts
					$record->stateArriving();
				}
			}
			elseif($record->isArriving())
			{
				$record->duration = $this->duration($record->departure_time, $updateDate);
				$nearby = $this->proximity($data['latitude'], $data['longitude']);
				if(!is_null($nearby) && $this->altitudeRange($data['altitude'], $nearby->elevation) && $data['groundspeed'] < 30) {
					// Airport is within range (20km), altitude is within elevation +/- 20ft and ground speed < 30 kts
					$record->stateArrived();
					$record->arrival_time = $updateDate;
					$record->setArrival($nearby);
				} else {
					// Make sure flight is marked as Airborne
					$record->stateAirborne();
				}
			}
			elseif($record->isPreparing())
			{
				if($data['longitude'] <> $record->last_lon || $data['latitude'] <> $record->last_lat || !$this->altitudeRange($data['altitude'], $record->lastPosition->altitude, 10)) {
					// Plane has moved (horizontally or vertically)
					$record->stateDeparting();
				}
			}

			if($record->isDeparting() && $data['altitude'] >= ($record->lastPosition->altitude + 50)) {
				// Flight is departing and altitude has increased by more than 50 ft
				$record->stateAirborne();
				$record->departure_time = $updateDate;
				$record->arrival_time = Carbon::instance($updateDate)->addHours($data['planned_hrsenroute'])->addMinutes($data['planned_minenroute']);
			}

			if(empty($record->departure_id)) {
				$record->departure_id = $data['planned_depairport'];
				$record->departure_id = $this->getAirports($data['planned_depairport']);
			}

			if(empty($record->arrival_id)) {
				$record->arrival_id = $data['planned_destairport'];
				$record->arrival_country_id = $this->getAirports($data['planned_destairport']);
			}

			$record->route = $data['planned_route'];
			$record->remarks = $data['planned_remarks'];
			$record->flighttype = $data['planned_flighttype'];

			// Aircraft codes
			$record->aircraft_code = $data['planned_aircraft'];
			$record->aircraft_id = $this->extractAircraft($data['planned_aircraft']);
			
			// Constantly update filed altitude, speed from flight plan
			$record->altitude = $data['planned_altitude'];
			$record->speed = $data['planned_tascruise'];

			// Update distance flown
			if($record->last_lat != 0 && $record->last_lon != 0) $record->distance += acos(sin(deg2rad($record->last_lat)) * sin(deg2rad($data['latitude'])) + cos(deg2rad($record->last_lat)) * cos(deg2rad($data['latitude'])) * cos(deg2rad($record->last_lon) - deg2rad($data['longitude']))) * 6371;
			
			// Update latest coordinates
			$record->last_lat = $data['latitude'];
			$record->last_lon = $data['longitude'];

			// Ensure flight is not marked as missing
			$record->missing = false;
			
			$record->save();

			// Create position report
			$this->positionReport($data, $record->id);
		}

		$thisYear = Flight::where('startdate','LIKE',date('Y') . '%')->count();
		$lastYear = Flight::where('startdate','LIKE',date('Y',strtotime('last year')) . '%')->count();

		Cache::forever('vatsim.year', number_format(Flight::where('startdate','LIKE',date('Y') . '%')->count()));
		Cache::forever('vatsim.month', number_format(Flight::where('startdate','LIKE',date('Y-m') . '%')->count()));
		Cache::forever('vatsim.day', number_format(Flight::where('startdate','=',date('Y-m-d'))->count()));
		Cache::forever('vatsim.distance', number_format(Flight::where('startdate','=',date('Y-m-d'))->sum('distance')));

		if($lastYear == 0) {
			Cache::forever('vatsim.change', '&infin;&nbsp;');
			Cache::forever('vatsim.changeDirection', 'up');
		} else {
			$percentageChange = (($thisYear - $lastYear) / $lastYear * 100);
			Cache::forever('vatsim.change', number_format(abs($percentageChange)));
			Cache::forever('vatsim.changeDirection', ($percentageChange > 0) ? 'up' : 'down');
		}
		
		Log::info('VATSIM UPDATE: END PILOTS');

		$this->cleanUpPilots($datas);
		// return;
		Log::info('VATSIM UPDATE: BEGIN CONTROLLERS');
		$datas = $this->getVatsimControllers();
		foreach($datas as $data)
		{
			if(empty($data['callsign']) || empty($data['cid'])) continue;

			$record = ATC::with('pilot')->whereCallsign($data['callsign'])->whereVatsimId($data['cid'])->whereNull('end')->first();

			if(is_null($record)) {
				$record = new ATC;
				$record->vatsim_id = $data['cid'];
				$record->callsign = $data['callsign'];
				$record->start = $updateDate;
				$record->facility_id = (ends_with($data['callsign'], '_ATIS')) ? 99 : $data['facilitytype'];
				$record->rating_id = $data['rating'];
				$record->visual_range = $data['visualrange'];
				$record->lat = $data['latitude'];
				$record->lon = $data['longitude'];
				$record->frequency = $data['frequency'];
				$record->facility_id = (ends_with($data['callsign'], '_ATIS')) ? 99 : $data['facilitytype'];

				if($record->facility_id < 6) {
					$nearby = $this->proximity($data['latitude'], $data['longitude']);
					$record->airport_id = (is_null($nearby)) ? null : $nearby->id;
				}

				$this->pilot($data);
			} else {
				$record->duration = $this->duration($record->start, $updateDate);
			}

			$record->pilot->rating_id = $data['rating'];
			$record->pilot->save();
			
			$record->missing = false;
			$record->time = $updateDate;
			$record->save();
		}
		Log::info('VATSIM UPDATE: END CONTROLLERS');

		$this->cleanUpControllers($datas);
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

	function pilot($data) {
		$pilot = Pilot::whereVatsimId($data['cid'])->first();
		if(is_null($pilot)) {
			$pilot = new Pilot;
			$pilot->vatsim_id = $data['cid'];
			$pilot->name = $data['realname'];
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

	function cleanUpPilots($data) {
		Log::info('VATSIM CLEAN: START PILOTS');
		$callsigns = array_pluck($data, 'callsign');
		$callsigns = array_combine($callsigns, $callsigns);
		$flights = Flight::where('state','!=',2)->with('lastPosition')->get();
		foreach($flights as $flight) {
			if(!in_array($flight->callsign, $callsigns)) {
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
						}
					}

					$flight->save();
				}
			} else {
				unset($callsigns[$flight->callsign]);
			}
		}
		Log::info('VATSIM CLEAN: END PILOTS');
	}

	function cleanUpControllers($data) {
		Log::info('VATSIM CLEAN: START CONTROLLERS');
		$callsigns = array_pluck($data, 'callsign');
		$callsigns = array_combine($callsigns, $callsigns);
		$controllers = ATC::whereNull('end')->get();
		foreach($controllers as $controller) {
			if(!in_array($controller->callsign, $callsigns)) {
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
				unset($callsigns[$controller->callsign]);
			}
		}
		Log::info('VATSIM CLEAN: END CONTROLLERS');
	}

}
