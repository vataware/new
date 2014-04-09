<?php

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
		$vatsim->loadData();

		$general = $vatsim->getGeneralInfo()->toArray();
		$date = Carbon::createFromTimestampUTC($general['update']);

		Cache::forever('vatsim.pilots', $vatsim->getPilots()->count());
		Cache::forever('vatsim.atc', $vatsim->getControllers()->count());
		Cache::forever('vatsim.users', $vatsim->getPilots()->count() + $vatsim->getControllers()->count());

		if(!is_null(Update::whereTimestamp($date)->first())) {
			$this->info('This update is already in the database.');
			return;
		}

		$update = new Update;
		$update->timestamp = $date;
		$update->save();

		$airports = Airport::lists('country_id','id');

		$registrations = Registration::get()->each(function($registration) {
			$registration->prefix = str_replace('-', '', $registration->prefix);
			if(!$registration->regex) $registration->prefix .= '.*';
		});

		$airlines = Airline::get();

		$datas = $vatsim->getPilots()->toArray();
		foreach($datas as $data) {
			$date = Carbon::createFromFormat('YmdHis', $data['time_logon'], 'UTC');
			$record = Flight::whereCallsign($data['callsign'])->whereVatsimId($data['cid'])->whereStartdate($date->toDateString())->first();
			
			if(is_null($record)) {
				$record = new Flight;
				$record->callsign = $data['callsign'];
				$record->vatsim_id = $data['cid'];
				$record->startdate = $date->toDateString();
				$record->departure_id = $data['planned_depairport'];
				$record->departure_country_id = array_key_exists($data['planned_depairport'], $airports) ? $airports[$data['planned_depairport']] : '';
				$record->arrival_id = $data['planned_destairport'];
				$record->arrival_country_id = array_key_exists($data['planned_destairport'], $airports) ? $airports[$data['planned_destairport']] : '';
				$record->route = $data['planned_route'];
				$record->remarks = $data['planned_remarks'];
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
			}
			
			if($data['planned_actdeptime'] > 0 && $data['planned_actdeptime'] < 2400) {
				$time = Carbon::instance($date)->startOfDay()->addHours(substr($data['planned_actdeptime'],0,-2))->addMinutes(substr($data['planned_actdeptime'],-2));
				$record->departure_time = $time;
				$record->arrival_time = $time->addHours($data['planned_hrsenroute'])->addMinutes($data['planned_minenroute']);
			}

			$record->altitude = $data['altitude'];
			$record->speed = $data['groundspeed'];
			$record->last_lat = $data['latitude'];
			$record->last_lon = $data['longitude'];
			$record->state = ($data['planned_hrsenroute'] > 0 || $data['planned_minenroute'] > 0) ? 1 : 0;
			
			$record->save();

			$position = new Position;

			$position->flight_id = $record->id;
			$position->update_id = $update->id;
			$position->lat = $data['latitude'];
			$position->lon = $data['longitude'];
			$position->altitude = $data['altitude'];
			$position->speed = $data['groundspeed'];
			$position->heading = $data['heading'];

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
