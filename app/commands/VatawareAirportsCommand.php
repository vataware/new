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

class VatawareAirportsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vataware:airports';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fix airports for departures/arrivals';

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
		$flights = Flight::with('positions')->whereDepartureId('')->orWhere('arrival_id','=','')->get();
		foreach($flights as $flight) {
			if(empty($flight->departure_id) && !is_null($position = $flight->positions->first())) {
				$nearby = Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $position->lat . ")) + cos(radians(`lat`)) * cos(radians(" . $position->lat . ")) * cos(radians(`lon`) - radians(" . $position->lon . "))) * 6371 AS distance"))
				->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $position->lat . ")) + cos(radians(`lat`)) * cos(radians(" . $position->lat . ")) * cos(radians(`lon`) - radians(" . $position->lon . "))) * 6371 < 20")
				->orderBy('distance','asc')
				->first();

				if(!is_null($nearby) && (($position->altitude > ($nearby->elevation - 20) && $position->altitude < ($nearby->elevation + 20) && $position->groundspeed < 30) || ($position->groundspeed == 0))) {
					$flight->departure_id = $nearby->id;
					$flight->departure_country_id = $nearby->country_id;
				}
			}

			if(!$flight->isDeparting() && !$flight->isPreparing() && empty($flight->arrival_id) && !is_null($position = $flight->positions->last())) {
				$nearby = Airport::select(DB::raw('*'), DB::raw("acos(sin(radians(`lat`)) * sin(radians(" . $position->lat . ")) + cos(radians(`lat`)) * cos(radians(" . $position->lat . ")) * cos(radians(`lon`) - radians(" . $position->lon . "))) * 6371 AS distance"))
				->whereRaw("acos(sin(radians(`lat`)) * sin(radians(" . $position->lat . ")) + cos(radians(`lat`)) * cos(radians(" . $position->lat . ")) * cos(radians(`lon`) - radians(" . $position->lon . "))) * 6371 < 20")
				->orderBy('distance','asc')
				->first();

				if(!is_null($nearby) && (($position->altitude > ($nearby->elevation - 20) && $position->altitude < ($nearby->elevation + 20) && $position->groundspeed < 30) || ($position->groundspeed == 0))) {
					$flight->arrival_id = $nearby->id;
					$flight->arrival_country_id = $nearby->country_id;
				}
			}

			$flight->save();
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
