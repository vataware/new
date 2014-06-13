<?php namespace Vataware\FlightPlan;

class FlightPlan
{
	protected $waypoints;

	public function __construct($route = '', $fromlat, $fromlng)
	{
		if(strlen($route) === 0)
			return array();

		// Remove multiple whitespaces, SID and STAR and replace dots with spaces
		$route = preg_replace('/\s+/', ' ',$route);
		$route = str_replace(array('SID','STAR'), '', $route);
		$route = str_replace('.', ' ', $route);
		
		$navpoints = explode(' ', $route);
		$navpoints = array_map(array($this, 'clean'), $navpoints);
		
		$allpoints = array();
		$total = count($navpoints);
		$airways = $this->airways($navpoints);
		
		for($i = 0; $i < $total; $i++) {
			$ident = $navpoints[$i];
			if(isset($airways[$ident])) {
				$entryName = $navpoints[$i-1];
				$entry = $this->getPointIndex($entryName, $airways[$ident]);

				$exitName = $navpoints[$i+1];
				$exit = $this->getPointIndex($exitName, $airways[$ident]);
					
				if($exit === false) {
					continue;
				} elseif($entry === false) {
					$entry = $exit;
				} else {
					$allpoints[$entryName] = $airways[$ident][$entry];
				}

				if($entry !== $exit) {
					if($entry < $exit) $points = array_slice($airways[$ident], $entry, $exit - $entry);
					else $points = array_reverse(array_slice($airways[$ident], $exit, $entry - $exit));

					foreach($points as $point)
						$allpoints[$point->ident] = $point;

				} else {
					$point = $airways[$ident][$entry];
					$allpoints[$point->ident] = $point;
				}
				
				$allpoints[$exitName] = $airways[$ident][$exit];
			} else {
				if(isset($allpoints[$navpoints[$i]])) {
					continue;
				}
				
				if(str_contains($navpoints[$i], '/')) {
					$ident = $navpoints[$i];
					$idents = explode('/', $ident);
					
					preg_match('/^([0-9]+)([A-Za-z]+)/', $idents[0], $matches);
				
					$lat = $matches[2] . $matches[1][0] . $matches[1][1] . '.' . $matches[1][2] . $matches[1][3];
					
					preg_match('/^([0-9]+)([A-Za-z]+)/', $idents[1], $matches);
					if($matches == 0)
						continue;

					$lon = $matches[2] . $matches[1][0] . $matches[1][1] . $matches[1][2] . '.' . $matches[1][3];
					
					$coords = $this->get_coordinates($lat . ' ' . $lon);
					
					if(in_array("", $coords)) {
						unset($allpoints[$navpoints[$i]]);
						continue;
					}
					
					$point = new Navdata;
					$point->ident = $ident;
					$point->name = $ident;
					$point->lat = $coords[0];
					$point->lon = $coords[1];
					$point->type = 6;
					$point->save();
					
					$allpoints[$navpoints[$i]] = $point;
				} else {
					$allpoints[$ident] = $ident;
					$list[] = $ident;
				}
			}
		}
		
		$details = $this->getNavDetails($list);
		
		foreach($allpoints as $name => &$point) {
			if(array_key_exists($name, $details)) {
				$point = $details[$name];
			}

			if(is_string($point)) {
				unset($allpoints[$name]);
				continue;
			}
			
			if(!is_array($point)) {
				continue;
			}
			
			$count = count($point);
			
			if($count == 1) {
				$point = $point[0];
			} elseif($count > 1) {
				$lowest = $point[0];
				$lowest_distance = $this->distanceBetweenPoints($fromlat, $fromlng, $lowest->lat, $lowest->lon);
				$lowest = 0;
				
				foreach($point as $index => $p) {
					$distance = $this->distanceBetweenPoints($fromlat, $fromlng, $p->lat, $p->lon);
					
					if($distance < $lowest_distance) {
						$lowest = $p;
						$lowest_distance = $distance;
					}
				}
				
				$point = $lowest;
			}
			
			$fromlat = $point->lat;
			$fromlng = $point->lon;
		}
		
		$this->waypoints = $allpoints;
	}

	public function get() {
		return $this->waypoints;
	}

	public function toArray() {
		$new = array();

		foreach($this->waypoints as $wpt => $data) {
			$new[] = [
				'ident' => $data->ident,
				'name' => $data->ident == $data->name ? '' : $data->name,
				'airway' => $data->airway,
				'lat' => $data->lat,
				'lon' => $data->lon,
				'freq' => $data->freq,
				'type' => $data->type,
			];
		}

		return $new;
	}

	public function map() {
		$positions = array();

		foreach($this->waypoints as $data) {
			$positions[] = 'new google.maps.LatLng(' . $data->lat . ', ' . $data->lon . ')';
		}

		return implode(',', $positions);
	}

	public function toString() {
		return implode(' ', array_keys($this->waypoints));
	}

	public function __toString() {
		return $this->toString();
	}
	
	protected function clean($ident) {
		$ident = strtoupper(trim($ident));
		return (str_contains($ident, '/')) ? explode('/', $ident)[0] : $ident;
	}
	
	protected function airways($waypoints) {
		return Navdata::whereIn('airway', $waypoints)->orderBy('seq')->get()->groupBy('airway');
	}
	
	protected function getPointIndex($point_name, $list) {
		$total = count($list);
		
		for($i=0; $i<$total; $i++) {
			if($list[$i]->ident == $point_name) {
				return $i;
			}
		}
		
		return false;
	}
	
	protected function getNavDetails($navpoints) {
		if(is_array($navpoints) && count($navpoints) > 0) {
			$in_clause = array();
			foreach($navpoints as $point) {
				if(is_array($point) || is_object($point))
					continue;
					
				$in_clause[] = $point;
			}
		} else {
			$in_clause = explode(' ', $navpoints);
		}
		
		$results = Navdata::whereIn('ident', $in_clause)->groupBy('lat')->get();
		
		if(!$results) {
			return array();
		}
		
		$return_results = array();
		foreach($results as $key => $point) {	
			if(empty($point->name)) {
				$point->name = $point->ident;
			}
			
			$return_results[$point->ident][] = $point;
		}
		
		return $return_results;
	}

	protected function distanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
		$lat1 = deg2rad(floatval($lat1));
		$lat2 = deg2rad(floatval($lat2));
		$lon1 = deg2rad(floatval($lon1));
		$lon2 = deg2rad(floatval($lon2));
		
		$a = sin(($lat2 - $lat1) / 2);
		$b = sin(($lon2 - $lon1) / 2);
		$h = pow($a, 2) + cos($lat1) * cos($lat2) * pow($b, 2);
		$distance = 2 * asin(sqrt($h)) * 3443.92;
			
		return $distance;
	}

	protected function coordinates($string) {
		preg_match('/^([A-Za-z])(\d*).(\d*.\d*).([A-Za-z])(\d*).(\d*.\d*)/', $string, $matches);
		list(, $latDirection, $latDegrees, $latMinutes, $lonDirection, $lonDegrees, $lonMinutes) = $matches;
		
		$latDegrees += $latMinutes / 60;
		if(strcasecmp($latDirection, 's') === 0)
			$latDegrees *= -1;

		$lonDegrees += $lonMinutes / 60;
		if(strcasecmp($lonDirection, 'w') === 0)
			$lonDegrees *= -1;
		
		return [$latDegrees, $lonDegrees];
	}
}