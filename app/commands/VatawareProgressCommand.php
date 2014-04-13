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

class VatawareProgressCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vataware:progress';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fix progress (distance/duration) for finished flights';

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
		$this->line('Start');
		$flights = Flight::whereState(2)->get();
		$this->line('Flights found: ' . $flights->count());

		foreach($flights as $flight) {
			$this->line($flight->callsign);
			if(!is_null($flight->departure_time) && !is_null($flight->arrival_time)) 
				$flight->duration = $this->duration($flight->departure_time, $flight->arrival_time);

			$this->line('--- Duration: ' . $flight->duration);

			$this->line('--- Positions: ' . $flight->positions->count());
			$distance = 0;
			foreach($flight->positions as $key => $position) {
				if($key > 0) $distance += $this->distance($position->lat, $position->lon, $previous->lat, $previous->lon);
				
				$previous = $position;
			}
			$flight->distance = $distance;
			$this->line('--- Distance: ' . $flight->distance);

			$flight->save();
			$this->line('');
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

	function duration($start, $now) {
		return $start->diffInMinutes($now);
	}

	function distance($lat1, $lon1, $lat2, $lon2) {
		return acos(sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon1) - deg2rad($lon2))) * 6371;
	}

}
