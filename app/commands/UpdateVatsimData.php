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

class UpdateVatsimData extends Command {

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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$vatsim = new Vatsimphp\VatsimData();
		$vatsim->setConfig('forceDataRefresh',true);
		$vatsim->loadData();

		$general = $vatsim->getGeneralInfo()->toArray();
		$updateDate = Carbon::createFromTimestampUTC($general['update']);

		Cache::forever('vatsim.pilots', $vatsim->getPilots()->count());
		Cache::forever('vatsim.atc', $vatsim->getControllers()->count());
		Cache::forever('vatsim.users', $vatsim->getPilots()->count() + $vatsim->getControllers()->count());

		if(!is_null(Update::whereTimestamp($updateDate)->first())) {
			Log::info('VATSIM Data not updated: already exists');
			$this->info('This update is already in the database.');
			return;
		}

		$update = new Update;
		$update->timestamp = $updateDate;
		$update->save();

		$airports = Airport::lists('country_id','id');

		$registrations = Registration::get()->each(function($registration) {
			$registration->prefix = str_replace('-', '', $registration->prefix);
			if(!$registration->regex) $registration->prefix .= '.*';
		});

		$airlines = Airline::get();

		$datas = $vatsim->getPilots()->toArray();
		foreach($datas as $data) {
			if(empty($data['longitude']) || empty($data['latitude'])) continue;

			$date = Carbon::createFromFormat('YmdHis', $data['time_logon'], 'UTC');
			$record = Flight::whereCallsign($data['callsign'])->whereVatsimId($data['cid'])->whereStartdate($date->toDateString())->with('lastPosition')->first();
			
			if(is_null($record)) {
				$record = new Flight;
				$record->callsign = $data['callsign'];
				$record->vatsim_id = $data['cid'];
				$record->startdate = $date->toDateString();
				if(empty($data['planned_depairport'])) {
					$nearby = Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $data['latitude'] . ")) + cos(radians(`lat`)) * cos(radians(" . $data['latitude'] . ")) * cos(radians(`lon`) - radians(" . $data['longitude'] . "))) * 6371 AS distance"))
					->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $data['latitude'] . ")) + cos(radians(`lat`)) * cos(radians(" . $data['latitude'] . ")) * cos(radians(`lon`) - radians(" . $data['longitude'] . "))) * 6371 < 20")
					->orderBy('distance','asc')
					->first();
					if(!is_null($nearby)) {
						$record->departure_id = $nearby->id;
						$record->departure_country_id = $nearby->country_id;
					}
				} else {
					$record->departure_id = $data['planned_depairport'];
					$record->departure_country_id = array_key_exists($data['planned_depairport'], $airports) ? $airports[$data['planned_depairport']] : '';
				}
				$record->arrival_id = $data['planned_destairport'];
				$record->arrival_country_id = array_key_exists($data['planned_destairport'], $airports) ? $airports[$data['planned_destairport']] : '';
				$record->route = $data['planned_route'];
				$record->remarks = $data['planned_remarks'];
				$record->flighttype = $data['planned_flighttype'];
				$record->state = 4;
				$record->aircraft_code = $data['planned_aircraft'];
				if(empty($data['planned_aircraft'])) {
					$record->aircraft_id = null;
				} else {
					preg_match('/(?:.\/)?([^\/]+)(?:\/.)?/', $data['planned_aircraft'], $aircraft);
					$record->aircraft_id = $aircraft[1];
				}

				$callsign = str_replace('-','',strtoupper($data['callsign']));

				// Airline
				$airline = $airlines->first(function($key, $airline) use ($callsign) {
					return preg_match('/^' . $airline->icao . '[0-9]{1,5}[A-Z]{0,2}$/', $callsign);
				});

				if(!is_null($airline)) {
					$record->airline_id = $airline->icao;
					$record->callsign_type = 1;
				} else {
					// Private
					$registration = $registrations->first(function($key, $registration) use ($callsign) {
						return preg_match('/^' . $registration->prefix . '$/', $callsign);
					});

					if(is_null($registration)) {
						$record->callsign_type = 0;

					} else {
						$record->callsign_type = 2;
						$record->airline_id = $registration->country_id;
					}
				}
			} elseif($record->state == 1 || $record->state == 3) {
				$nearby = Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $data['latitude'] . ")) + cos(radians(`lat`)) * cos(radians(" . $data['latitude'] . ")) * cos(radians(`lon`) - radians(" . $data['longitude'] . "))) * 6371 AS distance"))
				->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $data['latitude'] . ")) + cos(radians(`lat`)) * cos(radians(" . $data['latitude'] . ")) * cos(radians(`lon`) - radians(" . $data['longitude'] . "))) * 6371 < 20")
				->orderBy('distance','asc')
				->first();
				if(!is_null($nearby) && (is_null($record->lastPosition) || $data['altitude'] <= $record->lastPosition->altitude+20)) {
					// airport within 20km
					if($data['altitude'] > ($nearby->elevation - 20) && $data['altitude'] < ($nearby->elevation + 20) && $data['groundspeed'] < 30) {
						$record->state = ($record->state == 3) ? 2 : 3;
						if($record->state == 2) {
							$record->arrival_time = $updateDate;
							if(empty($record->arrival_id)) {
								$record->arrival_id = $nearby->id;
								$record->arrival_country_id = $nearby->country_id;
							}
						}
					} else {
						$record->state = 1;
					}
				} else {
					$record->state = 1;
				}
			} elseif($record->state == 4) {
				if($data['longitude'] <> $record->last_lon ||
				  $data['latitude'] <> $record->last_lat ||
				  ($data['altitude'] <= $record->lastPosition->altitude + 10 && $data['altitude'] >= $record->lastPosition->altitude - 10) ||
				  $data['groundspeed'] <> $record->lastPosition->speed) {
					$record->state = 0;
				}
			}

			if($record->state == 0 && ($data['planned_actdeptime'] > 0 || ($data['planned_deptime'] === "0" && $data['planned_actdeptime'] === "0")) && $data['altitude'] >= ($record->lastPosition->altitude + 50)) {
				$record->state = 1;
				if(is_null($record->departure_time)) {
					$record->departure_time = $updateDate;
				}
			}
			
			if($data['planned_actdeptime'] > 0 && $data['planned_actdeptime'] < 2400 && $record->state != 2) {
				$time = Carbon::instance($date)->startOfDay()->addHours(substr($data['planned_actdeptime'],0,-2))->addMinutes(substr($data['planned_actdeptime'],-2));
				$record->departure_time = $time;
				$record->arrival_time = $time->addHours($data['planned_hrsenroute'])->addMinutes($data['planned_minenroute']);
			}

			$record->altitude = $data['planned_altitude'];
			$record->speed = $data['planned_tascruise'];
			$record->last_lat = $data['latitude'];
			$record->last_lon = $data['longitude'];
			
			$record->save();

			$position = new Position;

			$position->flight_id = $record->id;
			$position->update_id = $update->id;
			$position->lat = $data['latitude'];
			$position->lon = $data['longitude'];
			$position->altitude = $data['altitude'];
			$position->speed = $data['groundspeed'];
			$position->heading = $data['heading'];
			// $position->ground_elevation = 
			$position->time = $updateDate;

			$position->save();

			$pilot = Pilot::whereVatsimId($data['cid'])->first();
			if(is_null($pilot)) {
				$pilot = new Pilot;
				$pilot->vatsim_id = $data['cid'];
				$pilot->name = $data['realname'];
				$pilot->save();
			}
		}

		Cache::forever('vatsim.year', number_format(Flight::where('startdate','LIKE',date('Y') . '%')->count()));
		Cache::forever('vatsim.month', number_format(Flight::where('startdate','LIKE',date('Y-m') . '%')->count()));
		Cache::forever('vatsim.day', number_format(Flight::where('startdate','=',date('Y-m-d'))->count()));
		Log::info('VATSIM Data updated');

		// return;

		$callsigns = array_pluck($datas, 'callsign');
		$callsigns = array_combine($callsigns, $callsigns);
		$flights = Flight::where('state','!=',2)->whereStartdate($date->toDateString())->with('lastPosition')->get();
		foreach($flights as $flight) {
			if(!in_array($flight->callsign, $callsigns)) {
			$this->comment('Flight #' . $flight->id);
			$this->info('--- State: ' . $flight->state);
			// flight missing

				if($flight->state == 1 || $flight->state == 3) {
				// airborne or near arrival
					$nearby = Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $flight->last_lat . ")) + cos(radians(`lat`)) * cos(radians(" . $flight->last_lat . ")) * cos(radians(`lon`) - radians(" . $flight->last_lon . "))) * 6371 AS distance"))
					->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $flight->last_lat . ")) + cos(radians(`lat`)) * cos(radians(" . $flight->last_lat . ")) * cos(radians(`lon`) - radians(" . $flight->last_lon . "))) * 6371 < 20")
					->orderBy('distance','asc')
					->first();
					if(!is_null($nearby) && !is_null($flight->lastPosition)) {
						// airport within 20km
						$this->info('--- Nearby airport: ' . $nearby->id);
						if($flight->lastPosition->altitude > ($nearby->elevation - 20) && $flight->lastPosition->altitude < ($nearby->elevation + 20) && $flight->lastPosition->groundspeed < 30) {
							$this->error('--- Arrived');
							$flight->state = 2;
							if(empty($record->arrival_id)) {
								$flight->arrival_id = $nearby->id;
								$flight->arrival_country_id = $nearby->country_id;
							}
							$flight->arrival_time = $flight->lastPosition->time;
							$flight->save();
							continue;
						}
					}
				}
				if(!is_null($flight->lastPosition) && Carbon::now()->diffInHours($flight->lastPosition->updated_at) < 1) {
					// flight has been missing for less than an hour
					$this->info('--- Time: ' . Carbon::now()->diffInMinutes($flight->lastPosition->updated_at) . ' minutes');
					$this->error('--- Marked as missing');
					$flight->missing = true;
					$flight->save();
				} else {
					// no record of last position
					$this->error('--- Deleted');
					$flight->delete();
					continue;
				}
			} else {
				unset($callsigns[$flight->callsign]);
			}
			$this->info('');
		}
		Log::info('Flights cleaned up');
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

}
