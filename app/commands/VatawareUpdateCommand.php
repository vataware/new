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
	protected $nextUpdate;

	protected $vatsim = null;
	protected $pilots = null;
	protected $controllers = null;

	protected $airports = null;
	protected $airportsIcao = null;
	protected $airlines = null;
	protected $registrations = null;

	protected $positions = array();

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// // $this->error('cron:datafeed[' . $this->processId . '] - Started execution');
		$this->error('Start: ' . ($start = Carbon::now()));
		$this->line('--- Memory usage: ' . memory_get_usage());

		// First we need to load the VATSIM data get the timestamps
		$this->prepareVatsim();

		// Second, we need to check if the update will not be a
		// duplicate.
		$this->checkUpdate();

		// Third, we can load the database data such as airlines,
		// airports, aircraft and registrations.
		$this->prepareDatabase();

		// Now we can start processing data. Starting with the pilots...
		$this->pilots();

		// ... then the ATC Controllers
		$this->controllers();

		// Generate map code
		$this->map();

		// Get statistics
		$this->statistics();

		$this->error('End: ' . Carbon::now());
		$this->error('Time: ' . $start->diffInSeconds(Carbon::now()));

		// Clean up variables
		$this->cleanup();

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

	protected function prepareDatabase() {
		// Get database records for all airlines, airports, registrations
		$this->airlines = Airline::get();
		$this->line('--- Loaded airlines');
		// $this->airports = Airport::get();
		// $this->airportsIcao = $this->airports->lists('country_id','icao');
		// ksort($this->airportsIcao);
		$this->line('--- Loaded airports');
		$this->registrations = Registration::get()->each(function($registration) {
			$registration->prefix = str_replace('-', '', $registration->prefix);
			if(!$registration->regex) $registration->prefix .= '.*';
		});
		$this->line('--- Loaded registrations');
	}

	protected function prepareVatsim() {
		// Load VATSIM datafeed
		$vatsim = new Vatsimphp\VatsimData();
		$vatsim->setConfig('forceDataRefresh',true);
		$vatsim->loadData();

		$general = $vatsim->getGeneralInfo()->toArray();

		$this->updateDate = Carbon::createFromTimestampUTC($general['update']);
		$this->nextUpdate = Carbon::instance($this->updateDate)->addMinutes($general['reload']);
		$this->pilots = $vatsim->getPilots()->toArray();
		$this->controllers = $vatsim->getControllers()->toArray();
		$this->line('--- Loaded VATSIM Datafeed');
	}

	function duration($start, $now) {
		return $start->diffInMinutes($now);
	}

	function vatsimUser($vatsimId, $rating = false) {
		$user = Pilot::whereVatsimId($vatsimId)->first();

		if(is_null($user) || $rating === true) {
			$it = new XmlIterator\XmlIterator('https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=' . $vatsimId, 'user');
			$official = iterator_to_array($it)[0];
		}

		if(is_null($user)) {
			$user = new Pilot;
			$user->vatsim_id = $vatsimId;
			$user->name = (string) $official['name_first'] . ' ' . (string) $official['name_last'];
			$user->rating_id = (string) $official['rating'];
			$user->save();
		} elseif($rating === true && !is_array($official['rating'])) {
			$user->rating_id = $official['rating'];
			$user->save();
		}

		unset($user);
	}

	function proximity($latitude, $longitude, $expects = null, $range = 20) {
		if(empty($latitude) || empty($longitude)) return null;

		if ($expects) {

			$arrival_apt = Airport::select('lat', 'lon')->where('icao', $expects)->first();
			$dtg = acos(sin(deg2rad($latitude)) * sin(deg2rad($arrival_apt->lat)) + cos(deg2rad($latitude)) * cos(deg2rad($arrival_apt->lat)) * cos(deg2rad($longitutde) - deg2rad($arrival_apt->lon))) * 6371;

			if(!is_null($arrival_apt) && ($dtg <= $range)) {
				return $arrival_apt;
			} else {
				return null;
			}
		} else {
			// For controllers or actual proximity
			return Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $latitude . ")) + cos(radians(`lat`)) * cos(radians(" . $latitude . ")) * cos(radians(`lon`) - radians(" . $longitude . "))) * 6371 AS distance"))
				->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $latitude . ")) + cos(radians(`lat`)) * cos(radians(" . $latitude . ")) * cos(radians(`lon`) - radians(" . $longitude . "))) * 6371 < " . $range)
				->orderBy('distance','asc')
				->first();
		}
	}

	protected function altitudeRange($altitude, $base, $range = 20) {
		return ($altitude >= $base - $range && $altitude <= $base + $range);
	}

	/**
	 * Checks if the timestamp of this update has already been
	 * processed, if yes then we will exit, otherwise we will add
	 * it to the database.
	 *
	 * @return void
	 */
	protected function checkUpdate() {
		// If the next update is expected at another timestamp then
		// we will exit the program and wait for a valid timestamp.
		if(Cache::has('vatsim.nextupdate') && Carbon::now()->lt(Cache::get('vatsim.nextupdate'))) {
			Log::info('Exit before next update ' . Cache::get('vatsim.nextupdate'));
			exit(0);
		}

		// If the update already exists with the current timestamp
		// then we would want to exit the program as we do not want
		// any duplicate position reports
		if(!is_null(Update::whereTimestamp($this->updateDate)->first())) {
			Log::info('Exit already exists ' . $this->updateDate);
			exit(0);
		}

		// Otherwise the new update time will be added to the database
		$update = new Update;
		$update->timestamp = $this->updateDate;
		$update->save();

		// Store the update ID in a class variable for position reports
		$this->updateId = $update->id;

		// Store the next update timestamp in the database
		Cache::forever('vatsim.nextupdate', $this->nextUpdate);
	}

	/**
	 * Processes the flights in the datafeed, both new and existing
	 * in the database. As well as any flights in the database that
	 * have not arrived yet but are missing from the datafeed.
	 *
	 * @return void
	 */
	protected function pilots() {
		// $this->error('Pilots: begin');
		// $this->error('Processing pilots');
		// First we will select all flights from the database which
		// have not yet been marked as arrived and are not missing.
		$database = Flight::where('state','!=','2')->get();

		$insert = array();
		$update = array();

		$default = array(
			'route' => '',
			'remarks' => '',
			'altitude' => '',
			'speed' => '',
			'flighttype' => 'I',
			'last_lat' => '0',
			'last_lon' => '0',
			'last_altitude' => '0',
			'last_speed' => '0',
			'last_heading' => '0',
			'missing' => '0',
			'startdate' => date('Y-m-d'),
			'revision' => '0',
			'callsign' => '',
			'callsign_type' => '0',
			'airline_id' => null,
			'vatsim_id' => '',
			'aircraft_code' => '',
			'aircraft_id' => null,
			'departure_id' => '',
			'arrival_id' => '',
			'state' => '4',
			'departure_time' => '',
			'arrival_time' => '',
			'departure_country_id' => '',
			'arrival_country_id' => '',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		);

		foreach($this->pilots as $entry) {
			$this->line('--- Entry ' . $entry['callsign'] . ' by ' . $entry['cid']);
			try {
				// Find the flight in the data we fetched using the callsign
				// and vatsim id of the pilot. If the flight does not exist
				// in the database we will create a new one.
				$flightKey = null;
				$flight = $database->first(function($key, $flight) use ($entry, &$flightKey) {
					if(str_replace('-','',$flight->callsign) == str_replace('-','',$entry['callsign']) && $flight->vatsim_id == $entry['cid']) {
						$flightKey = $key;
						return true;
					}
					return false;
				}, new Flight);

				// Some data will have to be refreshed with every update of
				// the datafeed. Pilots have the ability to update the route,
				// remarks, altitude and speed at all times using a new flight plan.
				$flight->route = $entry['planned_route'];
				$flight->remarks = $entry['planned_remarks'];
				$flight->altitude = $entry['planned_altitude'];
				$flight->speed = $entry['planned_tascruise'];
				$flight->flighttype = $entry['planned_flighttype'];

				// Update last known coordinates
				$flight->last_lat = $entry['latitude'];
				$flight->last_lon = $entry['longitude'];
				$flight->last_altitude = $entry['altitude'];
				$flight->last_speed = $entry['groundspeed'];
				$flight->last_heading = $entry['heading'];

				// We also need to ensure that the flight is not marked as missing,
				// now that we have a record of the flight again.
				$flight->missing = 0;

				// If the flight does not exist we need to load the basic
				// data, such as date, pilot, callsign, aircraft, etc.
				if(!$flight->exists) {
					$flight->startdate = Carbon::createFromFormat('YmdHis', $entry['time_logon'], 'UTC')->toDateString();
					$flight->revision = $entry['planned_revision'];

					$callsign = $this->callsign($entry['callsign']);
					$flight->callsign = $callsign['callsign'];
					$flight->callsign_type = $callsign['callsign_type'];
					$flight->airline_id = $callsign['airline_id'];

					$flight->vatsim_id = $entry['cid'];

					$flight->aircraft_code = $entry['planned_aircraft'];
					$flight->aircraft_id = $this->aircraft($entry['planned_aircraft']);

					$flight->departure_id = $entry['planned_depairport'];
					$flight->arrival_id = $entry['planned_destairport'];

					$flight->state = 4;

					$this->vatsimUser($entry['cid']);

					try {
						if($entry['planned_deptime'] > 0 && $entry['planned_deptime'] < 2359 && strlen($entry['planned_deptime']) >= 3 && !empty($entry['planned_deptime']))
							$flight->departure_time = Carbon::createFromFormat('Y-m-d G:i',  $flight->startdate . ' ' . (strlen($entry['planned_deptime']) >= 3 ? substr($entry['planned_deptime'], 0, -2) : '0') . ':' . substr($entry['planned_deptime'], -2), 'UTC');
					} catch(InvalidArgumentException $e) {
						Log::warning($entry['planned_deptime']);
						Log::warning($e);
					}
				}

				// If the flight does exist, we will add a position report and
				// only update certain fields if they have changed, such as the
				// departure/arrival airport.
				else {
					// Update distance
					try {
						$flight->distance += acos(sin(deg2rad($flight->getOriginal('last_lat'))) * sin(deg2rad($entry['latitude'])) + cos(deg2rad($flight->getOriginal('last_lat'))) * cos(deg2rad($entry['latitude'])) * cos(deg2rad($flight->getOriginal('last_lon')) - deg2rad($entry['longitude']))) * 6371;
					} catch(ErrorException $e) {
						Log::debug($e);
						Log::debug('last_lat: ' . $flight->getOriginal('last_lat'));
						Log::debug('last_lon: ' . $flight->getOriginal('last_lon'));
						Log::debug('latitude: ' . $entry['latitude']);
						Log::debug('longitude: ' . $entry['longitude']);
					}

					// Add the position report
					$this->positionReport($entry, $flight->id);

					if($entry['planned_revision'] > $flight->revision) {
						// Only allow the departure airport/time to be updated if the
						// current state is preparing(4) or departing(0). If done after
						// that it's technically too late since they have already departed.
						if(in_array($flight->state, [0, 4])) {
							$flight->departure_id = $entry['planned_depairport'];
							if($entry['planned_deptime'] > 0 && $entry['planned_deptime'] < 2359 && !empty($entry['planned_deptime']))
								$flight->departure_time = Carbon::createFromFormat('Y-m-d G:i',  $flight->startdate . ' ' . (strlen($entry['planned_deptime']) >= 3 ? substr($entry['planned_deptime'], 0, -2) : '0') . ':' . substr($entry['planned_deptime'], -2), 'UTC');
						}


						// Similar to the departure airport, the arrival airport can only
						// be updated prior to arrival. That is, when the current state is
						// preparing(4), departing(0) or airborne(1).
						if(in_array($flight->state, [0, 1, 4]))
							$flight->arrival_id = $entry['planned_destairport'];
					}
				}

				// Update the arrival time to always be the planned flight time
				// from the departure time.
				if(!is_null($flight->departure_time))
					$flight->arrival_time = Carbon::instance($flight->departure_time)->addHours($entry['planned_hrsenroute'])->addMinutes($entry['planned_minenroute']);
				else
					$flight->arrival_time = null;


				// Workflow processes
				// Flight is preparing and plane has taken off
				if(($flight->state == 4 || $flight->state == 0) && $this->hasTakenOff($flight, $entry)) {
					$flight->state = 1;
					$flight->departure_time = $this->updateDate;
				}

				// Flight is preparing and plane has moved
				elseif($flight->state == 4 && $this->hasMoved($flight, $entry)) {
					$flight->state = 0;
				}

				// Flight is airborne and near airport
				elseif($flight->state == 1 && $this->hasLanded($flight, $entry)) {
					$flight->state = 3;
				}

				// Flight is arriving
				elseif($flight->state == 3) {
					$airport = $this->hasLanded($flight, $entry);

					// Mark flight as arrived when still near airport
					if($airport) {
						$flight->state = 5;
						$flight->arrival_id = $airport;
						$flight->arrival_time = $this->updateDate;
						$flight->duration = $this->duration($flight->departure_time, $flight->arrival_time);
					}

					// Mark flight as airborne again when no near airport anymore
					else
						$flight->state = 1;
				}

				// We want to get the airport country IDs as one of the last things
				// because they are dependent on the other information pertaining to
				// this flight and it is not needed for anything else.
				$flight->departure_country_id = $this->airportCountry($flight->departure_id);
				$flight->arrival_country_id = $this->airportCountry($flight->arrival_id);

				// Skip this record if the callsign is empty
				if(empty($flight->callsign))
					continue;

				// Add flight to update array
				elseif($flight->exists) {
					$update[$flight->id] = array_except($flight->toArray(), array('startdate','callsign','callsign_type','airline_id','vatsim_id','aircraft_code','aircraft_id','created_at','updated_at','deleted_at'));
					$database->forget($flightKey);
					$this->comment('Updated flight');
				}
				// Add flight to insert array, also set the created_at
				// and updated_at columns
				else {
					$flight->created_at = Carbon::now();
					$flight->updated_at = Carbon::now();
					$this->comment('State: ' . $flight->state);
					$insert[] = array_merge($default, array_except($flight->toArray(), array('deleted_at')));
					$this->comment('Inserted new flight');
				}

				unset($flight, $entry, $callsign);
			} catch(ErrorException $e) {
				$this->error('Failed');
				Log::error($e);
			} catch(InvalidArgumentException $e) {
				$this->error('Failed');
				Log::error($e);
			}
		}

		// Insert new flights into the flights table right away
		$this->progressiveInsert(new Flight, $insert);
		unset($insert, $default);
		$this->line('Inserted new records');

		// Create temporary flights table for records that are to be updated
		DB::statement("create temporary table if not exists flights_temp (
			`id` int(10) unsigned NOT NULL,
			`departure_id` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
			`arrival_id` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
			`departure_country_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			`arrival_country_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
			`route` text COLLATE utf8_unicode_ci NOT NULL,
			`remarks` text COLLATE utf8_unicode_ci NOT NULL,
			`altitude` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			`speed` smallint(6) NOT NULL,
			`flighttype` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'I',
			`state` tinyint(4) NOT NULL,
			`missing` tinyint(1) NOT NULL DEFAULT '0',
			`departure_time` datetime DEFAULT NULL,
			`arrival_time` datetime DEFAULT NULL,
			`duration` smallint(6) NOT NULL DEFAULT '0',
			`distance` smallint(6) NOT NULL DEFAULT '0',
			`processed` tinyint(1) NOT NULL DEFAULT '0',
			`revision` tinyint(4) NOT NULL DEFAULT '1',
			`last_lat` decimal(10,6) NOT NULL,
			`last_lon` decimal(10,6) NOT NULL,
			`last_altitude` mediumint(6) NOT NULL,
			`last_speed` smallint(4) unsigned NOT NULL,
			`last_heading` smallint(3) unsigned NOT NULL,
			PRIMARY KEY (`id`)
		)");
		$this->line('Created temporary table');

		// Insert flights to be updated into temporary table
		$this->progressiveInsert('flights_temp', $update);
		$this->line('Inserted updated records');

		// Update flights table with data in temporary table
		DB::statement("update flights dest, flights_temp src set
			dest.departure_id = src.departure_id,
			dest.arrival_id = src.arrival_id,
			dest.departure_country_id = src.departure_country_id,
			dest.arrival_country_id = src.arrival_country_id,
			dest.route = src.route,
			dest.remarks = src.remarks,
			dest.altitude = src.altitude,
			dest.speed = src.speed,
			dest.flighttype = src.flighttype,
			dest.state = src.state,
			dest.missing = 0,
			dest.departure_time = src.departure_time,
			dest.arrival_time = src.arrival_time,
			dest.duration = src.duration,
			dest.distance = src.distance,
			dest.processed = src.processed,
			dest.revision = src.revision,
			dest.last_lat = src.last_lat,
			dest.last_lon = src.last_lon,
			dest.last_altitude = src.last_altitude,
			dest.last_speed = src.last_speed,
			dest.last_heading = src.last_heading,
			dest.updated_at = CURRENT_TIMESTAMP()
		where dest.id = src.id");
		$this->line('Updated records');

		if(count($this->positions) > 0) {
			$this->progressiveInsert(new Position, $this->positions);
			unset($this->positions);
		}

		unset($update);

		$delete = array();
		$disappeared = array();

		$this->line($database->count() . ' flights missing');

		foreach($database as $missing) {
			$this->line('--- Entry ' . $missing->callsign . ' by ' . $missing->vatsim_id);
			if($missing->state == 5) {
				$missing->state = 2;
				$missing->save();
				$this->comment('Pemanently arrived');
			} elseif(Carbon::now()->diffInMinutes($missing->updated_at) >= 60) {
				$delete[] = $missing->id;
				$this->comment('Deleted');
			} else {
				$this->comment($missing->state);
				if(($missing->state == 1 || $missing->state == 3) && $airport = $this->hasLanded($missing)) {
					$this->comment($airport);
					$this->comment($missing->departure_id);
					$this->comment($missing->arrival_id);
					$missing->state = 2;
					$missing->missing = 0;
					$missing->arrival_id = $airport;
					$missing->arrival_time = $missing->updated_at;
					$missing->duration = $this->duration($missing->departure_time, $missing->arrival_time);
					$missing->save();

					$missing->pilot->counter++;
					$missing->pilot->distance += $missing->distance;
					$missing->pilot->duration += $missing->duration;
					$missing->pilot->save();
					$this->comment('Missing: landed');
				} elseif(!$missing->missing) {
					$disappeared[] = $missing->id;
					$this->comment('Disappeared');
				}
			}

			unset($airport, $missing);
		}

		// Delete flights that have been missing for more than an hour
		if(count($delete) > 0)
			Flight::destroy($delete);

		// Set flights to missing
		if(count($disappeared) > 0)
			Flight::whereIn('id', $disappeared)->update(array('missing' => '1'));

		unset($database, $delete, $disappeared, $missings);
		$this->error('Pilots: end');
	}

	/**
	 * Processes the ATC in the datafeed, both new and existing
	 * in the database. As well as any ATC in the database that
	 * have not finished yet but are missing from the datafeed.
	 *
	 * @return void
	 */
	function controllers() {
		$this->error('Controllers: begin');
		$database = ATC::whereNull('end')->get();

		$update = array();
		$insert = array();

		$default = array(
			'vatsim_id' => '',
			'callsign' => '',
			'atis' => '',
			'frequency' => '',
			'visual_range' => '0',
			'lat' => '0',
			'lon' => '0',
			'time' => $this->updateDate,
			'missing' => 0,
			'start' => $this->updateDate,
			'facility_id' => 0,
			'rating_id' => 1,
			'airport_id' => null,
			'sector_id' => null,
		);

		foreach($this->controllers as $entry) {
			$this->line('--- Entry ' . $entry['callsign'] . ' by ' . $entry['cid']);

			// Find the ATC duty in the data we fetched using the callsign
			// and vatsim id of the controllers. If the duty does not exist
			// in the database we will create a new one.
			$atc = $database->first(function($key, $atc) use ($entry) {
				return ($atc->callsign == $entry['callsign'] && $atc->vatsim_id == $entry['cid']);
			}, new ATC);

			$atc->atis = $entry['atis_message'];
			$atc->frequency = $entry['frequency'];
			$atc->visual_range = $entry['visualrange'];
			$atc->lat = $entry['latitude'];
			$atc->lon = $entry['longitude'];
			$atc->time = $this->updateDate;

			$atc->missing = false;

			if(!$atc->exists) {
				$atc->vatsim_id = $entry['cid'];
				$atc->callsign = $entry['callsign'];

				$atc->start = Carbon::createFromFormat('YmdHis', $entry['time_logon'], 'UTC');

				$atc->facility_id = (ends_with($entry['callsign'], '_ATIS')) ? 99 : $entry['facilitytype'];
				$atc->rating_id = $entry['rating'];

				$this->vatsimUser($entry['cid'], true);

				if($atc->facility_id == 6) {
					$sector = SectorAlias::select('sectors.code')->where('sector_aliases.code','=',explode('_', $entry['callsign'])[0])->join('sectors','sector_aliases.sector_id','=','sectors.id')->pluck('code');
					$atc->sector_id = (is_null($sector)) ? null : $sector;
					unset($sector);
				}
			}

			if($this->hasRelocated($atc, $entry) && $atc->facility_id < 6) {
				$nearby = $this->proximity($entry['latitude'], $entry['longitude']);
				$atc->airport_id = (is_null($nearby)) ? null : $nearby->id;
				unset($nearby);
			}

			// Skip this record if the callsign is empty
			if(empty($atc->callsign))
				continue;

			// Add atc to update array
			elseif($atc->exists) {
				$update[$atc->id] = array_except($atc->toArray(), array('start','callsign','vatsim_id','facility','facility_id','rating_id','sector_id','processed','pilot','created_at','updated_at','deleted_at'));
			}

			// Add atc to insert array, also set the created_at
			// and updated_at columns
			else {
				$atc->created_at = Carbon::now();
				$atc->updated_at = Carbon::now();
				$insert[] = array_merge($default, array_except($atc->toArray(), array('facility','deleted_at')));
			}
		}

		// Insert new atc into the atc table right away
		$this->progressiveInsert(new ATC, $insert);
		unset($insert, $default);
		$this->line('Inserted new records');

		// Create temporary atc table for records that are to be updated
		DB::statement("create temporary table if not exists atc_temp (
			`id` int(10) unsigned NOT NULL,
			`frequency` double(6,3) NOT NULL,
			`visual_range` smallint(6) NOT NULL,
			`lat` double(10,6) NOT NULL,
			`lon` double(10,6) NOT NULL,
			`missing` tinyint(1) NOT NULL DEFAULT '0',
			`airport_id` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
			`end` datetime DEFAULT NULL,
			`time` datetime NOT NULL,
			`atis` text COLLATE utf8_unicode_ci,
			`duration` smallint(6) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		)");
		$this->line('Created temporary table');

		$missings = $database->filter(function($atc) use ($update) {
			return (!array_key_exists($atc->id, $update));
		});

		$this->line(count($update) . ' update records');

		foreach($missings as $missing) {
			$this->line('--- Entry ' . $missing->callsign . ' by ' . $missing->vatsim_id . ' last seen ' . $missing->time);
			if(Carbon::now()->diffInMinutes($missing->time) >= 10) {
				$this->comment('--- Marked as done');
				$missing->end = $missing->time;
				try {
					$missing->duration = $this->duration($missing->start, $missing->end);
				} catch(InvalidArgumentException $e) {
					Log::warning($e);
					Log::debug('ATC ID ' . $missing->id);
					Log::debug('Start: ' . $missing->getOriginal('start'));
					Log::debug('End: ' . $missing->getOriginal('end'));
				}
				$missing->missing = false;

				$missing->pilot->counter_atc++;
				$missing->pilot->duration_atc += $missing->duration;
				$missing->pilot->save();
			} elseif(!$missing->missing) {
				$missing->missing = true;
			}

			$update[$missing->id] = array_except($missing->toArray(), array('start','callsign','vatsim_id','facility','facility_id','rating_id','sector_id','processed','pilot','created_at','updated_at','deleted_at'));

			unset($missing);
		}

		$this->line(count($update) . ' update (with missing) records');

		// Insert atc to be updated into temporary table
		$this->progressiveInsert('atc_temp', $update);
		$this->line('Inserted updated records');

		// Update atc table with data in temporary table
		DB::statement("update atc dest, atc_temp src set
			dest.frequency = src.frequency,
			dest.visual_range = src.visual_range,
			dest.lat = src.lat,
			dest.lon = src.lon,
			dest.airport_id = src.airport_id,
			dest.time = src.time,
			dest.missing = src.missing,
			dest.atis = src.atis,
			dest.duration = src.duration,
			dest.end = src.end,
			dest.updated_at = CURRENT_TIMESTAMP()
		where dest.id = src.id");
		$this->line('Updated records');

		unset($database, $update, $missings);

		$this->error('Controllers: end');
	}

	private function cleanup() {
		//cleanup everything from attributes
		$this->line('--- Memory usage: ' . memory_get_usage());
		foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
			unset($this->$clsVar);
		}
		exit('--- Memory usage: ' . memory_get_usage() . "\n");
	}

	/**
	 * Processes the callsign by checking if it is an airline,
	 * private registration or otherwise unknown. Outputs the
	 * processed callsign, its type and the airline ICAO code
	 * or country's ISO code.
	 *
	 * @param  string  $originalCallsign
	 * @return array
	 */
	function callsign($originalCallsign) {
		// Strip callsign of hyphens so we can search without
		// complications regarding the format.
		$callsign = str_replace('-','',strtoupper($originalCallsign));

		// For airlines we will use the stripped callsign and
		// search based on a prefix basis
		if(!is_null($airline = $this->airline($callsign)))
			return ['callsign' => $callsign, 'callsign_type' => 1, 'airline_id' => $airline->icao];

		// For private registrations we will return the original
		// callsign with the hyphens.
		elseif(!is_null($registration = $this->registration($callsign)))
			return ['callsign' => $originalCallsign, 'callsign_type' => 2, 'airline_id' => $registration->country_id];

		// If all else fails, callsign type is 0, airline_id is null
		// and the original callsign will be used.
		return ['callsign' => $originalCallsign, 'callsign_type' => 0, 'airline_id' => null];
	}

	/**
	 * Strips the slashes and other codes from the aircraft
	 * code to be left with the official ICAO code.
	 *
	 * @param  string  $code
	 * @return string
	 */
	protected function aircraft($code) {
		if(empty($code)) return '';

		// Get the aircraft code (between slashes)
		preg_match('/(?:.\/)?([^\/]+)(?:\/.)?/', $code, $aircraft);
		return $aircraft[1];
	}

	/**
	 * Gets the country ISO code for the airport queried. If the
	 * airport cannot be found it will return an empty string.
	 *
	 * @param  string  $icao
	 * @return string
	 */
	protected function airportCountry($icao) {
		// We need to return the country code or
		// empty if the airport cannot be found in our database

		$airport = Airport::whereIcao($icao)->first();

		return is_null($airport) ? '' : $airport->country_id;
	}

	/**
	 * Checks whether the plane has moved relative to the
	 * last known position.
	 *
	 * @param  \Flight  $flight
	 * @param  array    $datafeed
	 * @return boolean
	 */
	protected function hasMoved($flight, $datafeed) {
		return ($datafeed['longitude'] <> $flight->getOriginal('last_lon') || $datafeed['latitude'] <> $flight->getOriginal('last_lat'));
	}

	/**
	 * Checks whether the ATC controller has relocated
	 * to another location.
	 *
	 * @param  \ATC   $atc
	 * @param  array  $datafeed
	 * @return boolean
	 */
	protected function hasRelocated($atc, $datafeed) {
		return ($datafeed['longitude'] <> $atc->getOriginal('lon') || $datafeed['latitude'] <> $atc->getOriginal('lat'));
	}

	/**
	 * Checks whether the plane has taken off by comparing
	 * altitude.
	 *
	 * @param  \Flight  $flight
	 * @param  array    $datafeed
	 * @return boolean
	 */
	protected function hasTakenOff($flight, $datafeed) {
		return ($flight->exists && $datafeed['altitude'] >= ($flight->getOriginal('last_altitude') + 50));
	}

	/**
	 * Checks whether the plane has landed by checking
	 * proximity to airport, altitude and speed.
	 *
	 * @param  \Flight     $flight
	 * @param  array|null  $datafeed
	 * @return string|boolean
	 */
	protected function hasLanded($flight, $datafeed = null) {
		if(is_null($datafeed)) {
			$latitude = $flight->last_lat;
			$longitude = $flight->last_lon;
			$altitude = $flight->last_altitude;
			$speed = $flight->last_speed;
		} else {
			$latitude = $datafeed['latitude'];
			$longitude = $datafeed['longitude'];
			$altitude = $datafeed['altitude'];
			$speed = $datafeed['groundspeed'];
		}

		$this->comment(print_r(compact('latitude','longitude','altitude','speed'), true));

		$nearby = $this->proximity($latitude, $longitude, $flight->arrival_id);
		return (!is_null($nearby) && ($this->altitudeRange($altitude, $nearby->elevation) || $nearby->elevation > $altitude) && $speed < 30)
			? $nearby->icao
			: false;
	}

	/**
	 * Checks whether the callsign matches an airline on
	 * record using regular expression.
	 *
	 * @param  string  $callsign
	 * @return \Airline|null
	 */
	protected function airline($callsign) {
		return $this->airlines->first(function($key, $airline) use ($callsign) {
			return preg_match('/^' . $airline->icao . '[0-9]{1,5}[A-Z]{0,2}$/', $callsign);
		});
	}

	/**
	 * Checks whether the callsign matches a registration on
	 * record using regular expression.
	 *
	 * @param  string  $callsign
	 * @return \Registration|null
	 */
	protected function registration($callsign) {
		return $this->registrations->first(function($key, $registration) use ($callsign) {
			return preg_match('/^' . $registration->prefix . '$/', $callsign);
		});
	}

	/**
	 * Create a position report for a flight and store it
	 * in a class variable.
	 *
	 * @param  array  $data
	 * @param  int    $flightId
	 * @return void
	 */
	protected function positionReport($data, $flightId) {
		$position = new Position;

		$position->flight_id = $flightId;

		$position->lat = $data['latitude'];
		$position->lon = $data['longitude'];
		$position->altitude = $data['altitude'];
		$position->speed = $data['groundspeed'];
		$position->heading = $data['heading'];
		// $position->ground_elevation =

		$position->update_id = $this->updateId;

		$this->positions[] = $position->toArray();

		unset($position, $data, $flightId);
	}

	/**
	 * Prepares an array for the map and stores it in the database.
	 *
	 * @return void
	 */
	protected function map() {
		$this->error('Map: start');
		$flights = Flight::whereMissing(false)
			->whereIn('state',[1, 3])
			->join('pilots','flights.vatsim_id','=','pilots.vatsim_id')
			->select('flights.*','pilots.name')
			->get()
			->transform(function($flight) {
				return [
					'id' => $flight->id,
					'callsign' => $flight->callsign,
					'vatsim_id' => $flight->vatsim_id,
					'pilot' => $flight->name,

					// Terminals
					'departure' => $flight->departure_id,
					'arrival' => $flight->arrival_id,

					// Aircraft
					'aircraft_code' => $flight->aircraft_code,
					'aircraft_id' => $flight->aircraft_id,

					// Location
					'lat' => $flight->last_lat,
					'lon' => $flight->last_lon,

					// Movement
					'altitude' => $flight->last_altitude,
					'speed' => $flight->last_speed,
					'heading' => $flight->last_heading,
				];
			});

		Cache::forever('vatsim.map', $flights);

		unset($flights);
		$this->error('Map: end');
	}

	protected function statistics() {
		$this->error('Statistics: begin');
		// Count the number of records in the pilot and controller arrays.
		$pilots = count($this->pilots);
		$controllers = count($this->controllers);

		// Store the counts in the database.
		Cache::forever('vatsim.pilots', $pilots);
		Cache::forever('vatsim.atc', $controllers);
		Cache::forever('vatsim.users', $pilots + $controllers);

		// Count the number of flights for the current year.
		$thisYear = Flight::whereBetween('startdate',array(date('Y') . '-01-01', date('Y-m-d')))->count();
		Cache::forever('vatsim.year', number_format($thisYear));

		// Count the number of flights for this month
		$thisMonth = Flight::whereBetween('startdate',array(date('Y-m') . '-01', date('Y-m-t')))->count();
		Cache::forever('vatsim.month', number_format($thisMonth));

		// Count the number of flights for this day
		$thisDay = Flight::whereStartdate(date('Y-m-d'))->count();
		Cache::forever('vatsim.day', number_format($thisDay));

		// Sum of the total distance flown today.
		$distance = Flight::whereStartdate(date('Y-m-d'))->sum('distance') * 0.54;
		Cache::forever('vatsim.distance', number_format($distance));

		// Count the number of flights for the same period last year.
		$lastYear = Flight::whereBetween('startdate',array((date('Y')-1) . '-01-01', (date('Y')-1) . date('-m-d')))->count();

		if($lastYear == 0) {
			Cache::forever('vatsim.change', '&infin;&nbsp;');
			Cache::forever('vatsim.changeDirection', 'up');
		} else {
			$percentageChange = (($thisYear - $lastYear) / $lastYear * 100);
			Cache::forever('vatsim.change', number_format(abs($percentageChange)));
			Cache::forever('vatsim.changeDirection', ($percentageChange > 0) ? 'up' : 'down');
		}

		unset($thisYear, $thisMonth, $thisDay, $distance, $lastYear, $percentageChange);

		$this->error('Statistics: end');
	}

	function error($arg) {
		Log::info($arg);
		return parent::error($arg);
	}

	function progressiveInsert($table, $data) {
		if(is_scalar($table)) $model = DB::table($table);
		else {
			$model = $table;
			$table = get_class($model);
		}

		$remaining = count($data);
		$this->comment('Progressive insert on ' . $table . ': ' . $remaining);
		$step = 0;
		do {
			try {
				$model->insert(array_slice($data, 100 * $step, 100));
			} catch(ErrorException $e) {
				Log::error($e);
			}
			$this->comment('Progressive insert on ' . $table . ': ' . $remaining);
			$remaining -= 100;
			$step++;
		} while($remaining > 0);

		unset($remaining, $data, $step);
	}

}
