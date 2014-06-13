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

use Vataware\FlightPlan\Navdata;

class VatawareNavdataCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vataware:navdata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Load Navdata';

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
		$this->FSBuild();
		$this->FAA();
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
			array('fsbuild', null, InputOption::VALUE_REQUIRED, 'Path to FSBuild directory', null),
			array('faa', null, InputOption::VALUE_REQUIRED, 'Path to FAA directory', null),
			array('xplane', null, InputOption::VALUE_OPTIONAL, 'Path to XPlane directory', null),
		);
	}

	function FSBuild() {
		# Setup the inserts into chunks of 1000
		$chunksOrig = 250;

		Navdata::truncate();

		$total = 0;
		$list = array();
		$chunks = $chunksOrig;

		$this->line('Loading airways segments...');
		$handle = fopen($this->option('fsbuild') . '/fsb_awy2.txt', 'r');
		while ($line = fscanf($handle, "%s %s %s %s %s %s %s\n"))  {

			if($line[0][0] == ';') {
				continue;
			}

			list ($airway, $seq, $ident, $lat, $lon, $airway_type, $type) = $line;
			
			if($type == 'F' || $type == 'B') {
				$type = 5;
			} else {
				$type = 3;
			}
						
			$list[] = compact('ident','airway','airway_type','seq','lat','lon','type');

			$total++;

			if($total == $chunks) {
				Navdata::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
		}
		
		Navdata::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');

		fclose($handle);

		$this->line($total . ' airway segments loaded...');
		$this->line('Loading VORs...');

		$total = 0;
		$updated = 0;

		$updated_list = array();

		$list = array();
		$chunks = $chunksOrig;

		$handle = fopen($this->option('fsbuild') . '/fsb_vor.txt', 'r');
		while ($lineinfo = fscanf($handle, "%s %s %s %s %s %s\n"))  {
			if($lineinfo[0][0] == ';') {
				continue;
			}
			
			list ($ident, $name, $lat, $lon, $type, $freq) = $lineinfo;

			$lat = $this->coordinatesFSBuild($lat);
			$lon = $this->coordinatesFSBuild($lon);
				
			$name = ucwords(strtolower(str_replace('_', ' ', $name)));

			$type = 3;
			
			$res = Navdata::whereIdent($ident)->first();
			if(!is_null($res)) {
				
				if(in_array($ident, $updated_list)) {
					continue;
				} else {
					$updated_list[] = $ident;
				}

				$query = Navdata::whereIdent($ident)->whereBetween('lat',$this->coordinateRange($lat))->whereBetween('lon',$this->coordinateRange($lon))->update(array(
					'name' => $name,
					'freq' => $freq
				));

				$updated++;
				continue;
			} else { 
				$list[] = compact('ident','name','lat','lon','freq','type');
			}
			
			if($total == $chunks) {
				Navdata::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total ++;
		}

		Navdata::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');
		fclose($handle);

		$this->line($total . ' VORs added, ' . $updated . ' updated');
		$this->line('Loading NDBs...');

		// Add NDBs

		$total = 0;
		$chunks = $chunksOrig;
		$list = array();

		$handle = fopen($this->option('fsbuild') . '/fsb_ndb.txt', 'r');
		while ($lineinfo = fscanf($handle, "%s %s %s %s %s\n"))  {
			
			if($lineinfo[0][0] == ';') {
				continue;
			}
			
			list ($ident, $name, $lat, $lon, $type) = $lineinfo;
				
			$lat = $this->coordinatesFSBuild($lat);
			$lon = $this->coordinatesFSBuild($lon);
			
			$name = strtoupper($name);
			
			$type = 2;
			
			$res = Navdata::whereIdent($ident)->first();
			if(!is_null($res)) {
				if(in_array($ident, $updated_list)) {
					continue;
				} else {
					$updated_list[] = $ident;
				}

				Navdata::whereIdent($ident)->update(array(
					'name' => $name,
					'lat' => $lat,
					'lon' => $lon,
				));
								
				$updated++;
				continue;
			} else { 
				$list[] = compact('ident','name','lat','lon','type');
			}
			
			if($total == $chunks) {
				Navdata::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total++;
		}

		Navdata::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');

		fclose($handle);

		$this->line($total . ' NDBs added, ' . $updated . ' updated');
	}

	/**
	 * Import VOR and NDBs into our database
	 */

	function coordinatesFSBuild($line) {
		/* Get the lat/long */
		preg_match('/^([A-Za-z])(\d*):(\d*:\d*)/', $line, $coords);

		$lat_dir = $coords[1];
		$lat_deg = $coords[2];
		$lat_min = $coords[3];

		$lat_deg = ($lat_deg*1.0) + ($lat_min/60.0);

		if(strtolower($lat_dir) == 's')
			$lat_deg *= -1;

		if(strtolower($lat_dir) == 'w')
			$lat_deg *= -1;

		return $lat_deg;
	}

	function FAA() {
		$this->line('Parsing FAA Data');
		$fp = fopen($this->option('faa') . '/AWY.txt', 'r');

		$airways = array();

		$total = 0;
		$updated = 0;
		$skipped = 0;
		$inserted = 0;
		while($line = fgets($fp)) {
			$type = substr($line, 0, 4);
			if($type == 'AWY2') {
				if(strlen(trim(substr($line, 9, 1))) > 0) {
					$skipped++;
					continue;
				}

				$airway_name = trim(substr($line, 4, 5));
				$lat = trim(substr($line, 82, 14));
				$lon = trim(substr($line, 97, 14));
				$fix_name = trim(substr($line, 125, 5));
				preg_match('/\*([A-Z0-9]+)\*/', $fix_name, $matches);				
				if(!isset($matches[1])) {
					$skipped++;
					continue;
				}
				$fix_name = $matches[1];

				
				$lat = $this->coordinatesFAA($lat);
				$lon = $this->coordinatesFAA($lon);
				
				if(empty($fix_name) || empty($lat) || empty($lon)) {
					$skipped++;
					continue;
				}

				$res = Navdata::whereName($fix_name)->whereAirway($airway_name)->first();
						
				if(is_null($res)) {
					$skipped++;
					continue;
				}
				
				// Only update is there a within a 2% difference
				$lat_diff = abs((abs($lat / $res->lat)) * 100 - 100);
				$lon_diff = abs((abs($lon / $res->lon)) * 100 - 100);
				
				if($lat_diff > 1 || $lon_diff > 1) {
					$skipped++;
					continue;
				}

				$res->lat = $lat;
				$res->lon = $lon;
				$res->save();
										
				$updated++;
			}

			$total++;
		}

		$this->line('Entries parsed: ' . $total . ', ' . $updated . ' updated, ' . $skipped . ' skipped');
	}

	function coordinatesFAA($line) {
		/* Get the lat/long */
		preg_match('/^(\d*)-(\d*)-(\d*)\.(\d*)([A-Za-z])/', $line, $coords);

		$lat_deg = $coords[1];
		$lat_min = $coords[2];
		$lat_dir = $coords[5];

		$lat_deg = ($lat_deg*1.0) + ($lat_min/60.0);

		if(strtolower($lat_dir) == 's')
			$lat_deg = '-'.$lat_deg;

		if(strtolower($lat_dir) == 'w')
			$lat_deg = $lat_deg*-1;

		return number_format($lat_deg, 6);
	}

	function XPlane() {
		$fp = fopen('xplane/Resources/default data/earth_awy.dat', 'r');

		$list ='SELECT * FROM phpvms_navdata
				WHERE `lat`=0 OR `lng`=0';

		$list = Navdata::whereLat(0)->orWhereLng(0)->get();

		$missing = array();	
		foreach($list as $row) {
			$missing[$row->name] = array();
		}

		$airways = array();
		$total=0;
		$skip = 0;
		while($line = fgets($fp)) {
			if($skip < 3) {
				$skip ++;
				continue;
			}

			list($entry_name, $entry_lat, $entry_lng, $exit_name, $exit_lat, $exit_lng, 
					$hi_lo, $base, $top, $name) = explode(' ', $line);

			$entry_name = trim($entry_name);
			if(array_key_exists($entry_name, $missing)) {
				$missing[$entry_name] = array(
					'lat' => $entry_lat,
					'lng' => $entry_lng,
					'source' => 'awy',
				);

				$total++;
			}	
		}

		// Next check the fixes
		fclose($fp);

		$fp = fopen($this->option('xplane') . '/Resources/default data/earth_fix.dat', 'r');
		$skip = 0;
		while($line = fgets($fp)) {

			# Skip the first three lines
			if($skip < 3) {
				$skip ++;
				continue;
			}

			list($lat, $lng, $name) = explode(' ', $line);

			$name = trim($name);
			if(array_key_exists($name, $missing)) {
				$missing[$name] = array(
					'lat' => $lat,
					'lng' => $lng,
					'source' => 'fix',
				);

				$total++;
			}
		}

		fclose($fp);

		$fp = fopen($this->option('xplane') . '/Resources/default data/earth_nav.dat', 'r');
		$skip = 0;
		while ($fix_info = fscanf($fp, "%s %s %s %s %s %s %s %s %s %s %s\n"))  {

			if($fix_info[0] == '2') {
				$type = 2;
				$lat = $fix_info[1];
				$lng = $fix_info[2];
				$freq = $fix_info[4];
				$name = $fix_info[7];
				$title = $fix_info[8];
				$total_ndb ++;
			} elseif($fix_info[0] == '3') {
				$type = 3;
				$lat = $fix_info[1];
				$lng = $fix_info[2];
				$freq = $fix_info[4];
				$name = $fix_info[7];
				$title = $fix_info[8];
				$total_vor ++;
			}

			$name = trim($name);

			if(empty($lat) || empty($lng))
				continue;

			if(array_key_exists($name, $missing)) {

				$missing[$name] = array(
					'lat' => $lat,
					'lng' => $lng,
					'source' => 'nav',
				);

				$total++;
			}
		}

		fclose($fp);

		print_r($missing);

		echo "Total: ".count($missing).", updated {$total}\n";
	}

	function coordinateRange($coordinate) {
		$diff = abs($coordinate - (1.01 * $coordinate));
		return [$coordinate - $diff, $coordinate + $diff];
	}

}
