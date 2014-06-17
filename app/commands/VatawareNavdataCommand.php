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

use Vataware\FlightPlan\Airway;
use Vataware\FlightPlan\DepApp;
use Vataware\FlightPlan\Waypoint;

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

	protected $navs = array();

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
		$this->XPlane();
		$this->FSBuild();
		// $this->FAA();
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

		Airway::truncate();

		$total = 0;
		$list = array();
		$chunks = $chunksOrig;

		$this->line('Loading airways segments...');
		$handle = fopen($this->option('fsbuild') . '/fsb_awy2.txt', 'r');
		while ($line = fscanf($handle, "%s %s %s %s %s %s %s\n"))  {

			if($line[0][0] == ';') {
				continue;
			}

			list ($airway, $seq, $ident, /* lat */, /* lon */, $airway_type) = $line;
						
			$list[] = compact('ident','airway','airway_type','seq');

			$total++;

			if($total == $chunks) {
				Airway::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
		}
		
		Airway::insert($list);
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

			if(isset($this->navs[$ident])) {
				$latRange = $this->coordinateRange($lat);
				$lonRange = $this->coordinateRange($lon);

				foreach($this->navs[$ident] as $existing) {
					if($latRange[0] >= $existing['lat'] && $latRange[1] <= $existing['lat'] && $lonRange[0] >= $existing['lon'] && $existing['lon'] <= $lonRange[1]) {
						continue 2;
					}
				}
			}
				
			$name = ucwords(strtolower(str_replace('_', ' ', $name)));

			$type = 'V';
			$freq *= 100;
			
			$list[] = compact('ident','name','lat','lon','freq','type');
			
			if($total == $chunks) {
				Waypoint::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total++;
		}

		Waypoint::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');
		fclose($handle);

		$this->line($total . ' VORs added, ' . $updated . ' updated');
		
		$this->line('Loading NDBs...');

		// Add NDBs

		$total = 0;
		$chunks = $chunksOrig;
		$list = array();

		$handle = fopen($this->option('fsbuild') . '/fsb_ndb.txt', 'r');
		while ($lineinfo = fscanf($handle, "%s %s %s %s %s %s\n"))  {
			
			if($lineinfo[0][0] == ';') {
				continue;
			}
			
			list ($ident, $name, $lat, $lon, $type, $freq) = $lineinfo;
				
			$lat = $this->coordinatesFSBuild($lat);
			$lon = $this->coordinatesFSBuild($lon);

			if(isset($this->navs[$ident])) {
				$latRange = $this->coordinateRange($lat);
				$lonRange = $this->coordinateRange($lon);

				foreach($this->navs[$ident] as $existing) {
					if($latRange[0] >= $existing['lat'] && $latRange[1] <= $existing['lat'] && $lonRange[0] >= $existing['lon'] && $existing['lon'] <= $lonRange[1]) {
						continue 2;
					}
				}
			}
			
			$name = ucwords(strtolower(str_replace('_', ' ', $name)));
			
			$type = 'N';
			$list[] = compact('ident','name','lat','lon','type','freq');
			
			if($total == $chunks) {
				Waypoint::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total++;
		}

		Waypoint::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');

		fclose($handle);

		$this->line($total . ' NDBs added, ' . $updated . ' updated');

		$this->line('Loading SIDs...');
		$handle = fopen($this->option('fsbuild') . '/fsb_sids.txt', 'r');
		$currentAirport = null;
		$data = array();
		$total = 0;

		fscanfe($handle, '%s %s %s %s\n', function($lineinfo) use (&$data, &$currentAirport, &$total) {
			if($lineinfo[0][0] == ';' || $lineinfo[0][0] == '*' || is_null($lineinfo)) {
				return;
			} elseif($lineinfo[0][0] == '[') {
				$currentAirport = substr($lineinfo[0], 1, -1);
			} elseif($lineinfo[0] == 'T') {
				$total++;
				$data[$currentAirport . ':' . $lineinfo[1]] = array('airport' => $currentAirport, 'ident' => $lineinfo[1], 'runway' => $lineinfo[3], 'waypoints' => array());
			} else {
				$data[$currentAirport . ':' . $lineinfo[0]]['waypoints'][] = $lineinfo[1];
			}
		});

		foreach($data as $sid) {
			if(!array_key_exists('ident', $sid)) continue;

			$wpt = new DepApp;
			$wpt->ident = $sid['ident'];
			$wpt->airport_id = $sid['airport'];
			$wpt->runway = $sid['runway'];
			$wpt->route = implode(' ', $sid['waypoints']);
			$wpt->type = 'D';
			$sids[] = $wpt->toArray();
		}

		$this->line($total . ' SIDs added');

		fclose($handle);

		progressiveInsert(new DepApp, $sids);

		$this->line('Loading STARs...');
		$handle = fopen($this->option('fsbuild') . '/fsb_stars.txt', 'r');
		$currentAirport = null;
		$data = array();
		$total = 0;

		fscanfe($handle, '%s %s %s %s\n', function($lineinfo) use (&$data, &$currentAirport, &$total) {
			if($lineinfo[0][0] == ';' || $lineinfo[0][0] == '*' || is_null($lineinfo)) {
				return;
			} elseif($lineinfo[0][0] == '[') {
				$currentAirport = substr($lineinfo[0], 1, -1);
			} elseif($lineinfo[0] == 'T') {
				$total++;
				$data[$currentAirport . ':' . $lineinfo[1]] = array('airport' => $currentAirport, 'ident' => $lineinfo[1], 'runway' => $lineinfo[3], 'waypoints' => array());
			} else {
				$data[$currentAirport . ':' . $lineinfo[0]]['waypoints'][] = $lineinfo[1];
			}
		});

		foreach($data as $star) {
			if(!array_key_exists('ident', $star)) continue;

			$wpt = new DepApp;
			$wpt->ident = $star['ident'];
			$wpt->airport_id = $star['airport'];
			$wpt->runway = $star['runway'];
			$wpt->route = implode(' ', $star['waypoints']);
			$wpt->type = 'A';
			$stars[] = $wpt->toArray();
		}

		$this->line($total . ' STARs added');

		fclose($handle);

		progressiveInsert(new DepApp, $stars);
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
		Waypoint::truncate();

		$handle = fopen($this->option('xplane') . '/earth_fix.dat', 'r');
		$skip = 0;
		$chunksOrig = 250;
		$total = 0;
		$chunks = $chunksOrig;
		$list = array();
		while($line = fgets($handle)) {

			if($skip < 3) {
				$skip ++;
				continue;
			}

			$line = explode(' ', trim(preg_replace('/\s+/', ' ',$line)));
		
			if(count($line) < 3) continue;
		
			list($lat, $lon, $name) = $line;

			$name = trim($name);

			$list[] = array(
				'ident' => $name,
				'lat' => $lat,
				'lon' => $lon,
				'type' => 'F',
			);

			if($total == $chunks) {
				Waypoint::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total++;
		}

		Waypoint::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');

		$this->line('Loaded fixes: ' . $total);

		fclose($handle);

		$handle = fopen($this->option('xplane') . '/earth_nav.dat', 'r');
		$skip = 0;
		$chunksOrig = 250;
		$total = 0;
		$chunks = $chunksOrig;
		$list = array();
		while($line = fgets($handle)) {

			if($skip < 3) {
				$skip ++;
				continue;
			}

			$line = explode(' ', trim(preg_replace('/\s+/', ' ',$line)));
		
			if(count($line) < 3)
				continue;
		
			list($type, $lat, $lon, /* 3 */, $freq, /* 5 */, /* 6 */, $ident) = $line;
			$name = ucwords(strtolower(implode(' ', array_slice($line, 8, -1))));

			if(!in_array($type, [2, 3]))
				continue;

			$this->navs[$ident][] = $new[] = array(
				'ident' => $ident,
				'name' => $name,
				'lat' => $lat,
				'lon' => $lon,
				'freq' => $freq,
				'type' => ($type == 2) ? 'N' : 'V',
			);

			if($total == $chunks) {
				Waypoint::insert($list);
				$this->line('Inserted batch (' . $chunks . ')');
				$list = array();

				$chunks += $chunksOrig;
			}
			
			$total++;
		}

		Waypoint::insert($list);
		$this->line('Inserted batch (' . $chunks . ')');

		$this->line('Loaded VOR and NDB: ' . $total);

		fclose($handle);
	}

	function coordinateRange($coordinate) {
		$diff = abs($coordinate - (1.01 * $coordinate));
		return [$coordinate - $diff, $coordinate + $diff];
	}

}
