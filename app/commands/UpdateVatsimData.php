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

		if(!is_null(Update::whereTimestamp($date)->first())) {
			$this->info('This update is already in the database.');
			return;
		}

		$update = new Update;
		$update->timestamp = $date;
		$update->save();

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
				$record->arrival_id = $data['planned_destairport'];
				$record->route = $data['planned_route'];
				$record->remarks = $data['planned_remarks'];
				$record->aircraft_code = $data['planned_aircraft'];
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
