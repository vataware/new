<?php namespace Vataware\FlightPlan;

use Airport;

class FlightPlan
{
	protected $waypoints;

	public function __construct($route = '', $fromlat, $fromlng, $departure = null, $arrival = null)
	{
		if(strlen($route) === 0)
			return array();

		// Remove multiple whitespaces, SID and STAR and replace dots with spaces
		$route = preg_replace('/\s+/', ' ',$route);
		$route = str_replace(array('SID','STAR','+'), '', $route);
		$route = str_replace('.', ' ', $route);
		
		$navpoints = explode(' ', trim($route));

		$sid = explode('/', $navpoints[0])[0];
		$star = explode('/', last($navpoints))[0];
		$depapps = $this->depapps($sid, $star, $departure, $arrival);
		$sidstar = array();
		if(isset($depapps[$sid])) {
			$route = $depapps[$sid] . substr($route, strlen($sid));
			$sids = array_map(function($val) use ($sid) {
				return $sid;
			}, array_flip(explode(' ', $depapps[$sid])));
			$sidstar = $sids;
		}
		if(isset($depapps[$star])) {
			$route = substr($route, 0, -strlen($star)) . $depapps[$star];
			$stars = array_map(function($val) use ($star) {
				return $star;
			}, array_flip(explode(' ', $depapps[$star])));
			$sidstar = array_merge($sidstar, $stars);
		}

		$navpoints = explode(' ', trim($route));
		$navpoints = array_map(array($this, 'clean'), $navpoints);
		
		$allpoints = array();
		$total = count($navpoints);
		$airways = $this->airways($navpoints);
		
		for($i = 0; $i < $total; $i++) {
			$ident = $navpoints[$i];
			if($i > 0 && isset($airways[$ident])) {
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

					foreach($points as $point) {
						if(isset($sidstar[$point->ident])) {
							$point->airway = $sidstar[$point->ident];
						}
						$allpoints[$point->ident] = $point->waypoints;
					}
				} else {
					$point = $airways[$ident][$entry];
					$allpoints[$point->ident] = $point->waypoints;
				}
				
				$allpoints[$exitName] = $airways[$ident][$exit]->waypoints;
			} else {
				if(isset($allpoints[$navpoints[$i]])) {
					continue;
				}
				
				$ident = $navpoints[$i];

				$atlanticRoute = preg_match('/([A-Z0-9a-z]+)\/(?:[N|F|M]\d+)+/', $ident, $matches);
				if($atlanticRoute) {
					$ident = $matches[1];
				}

				$coordinates = preg_match('/(\d{1,3})([N|S])(\d{1,3})([E|W])/', $this->shortCoordinate($ident), $matches);
				
				if($coordinates) {
					$lat = $matches[1] * (($matches[2] == 'N') ? 1 : -1);
					$lon = $matches[3] * (($matches[4] == 'E') ? 1 : -1);
					
					$point = new Waypoint;
					$point->ident = $ident;
					$point->name = $ident;
					$point->lat = $lat;
					$point->lon = $lon;
					$point->type = 'T';
					$point->save();
					
					$allpoints[$navpoints[$i]] = $point;
					continue;
				}
				
				$allpoints[$ident] = $ident;
				$list[] = $ident;
			}
		}

		$details = $this->getNavDetails($list);

		foreach($allpoints as $name => &$point) {
			if($details->offsetExists($name)) {
				$point = $details[$name];
			}

			if(!is_array($point) && !$point instanceof \Illuminate\Database\Eloquent\Collection && !$point instanceof Waypoint) {
				unset($allpoints[$name]);
				continue;
			}

			$count = count($point);
			if($point instanceof Waypoint) {

			} elseif($count == 0) {
				unset($allpoints[$name]);
				continue;
			} elseif($count == 1) {
				$point = $point[0];
				if(isset($sidstar[$point->ident])) {
					$point->airway = $sidstar[$point->ident];
				}
			} elseif($count > 1) {
				$lowest = $point[0];
				$lowest_distance = $this->distanceBetweenPoints($fromlat, $fromlng, $lowest->lat, $lowest->lon);
				
				foreach($point as $index => $p) {
					
					$distance = $this->distanceBetweenPoints($fromlat, $fromlng, $p->lat, $p->lon);

					if($distance < $lowest_distance) {
						$lowest = $p;
						$lowest_distance = $distance;
					}
				}
				
				$point = $lowest;
				if(isset($sidstar[$point->ident])) {
					$point->airway = $sidstar[$point->ident];
				}
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

	public function map(Airport $departure = null, Airport $arrival = null) {
		$positions = array();

		if(!is_null($departure)) {
			$positions[] = 'new google.maps.LatLng(' . $departure->lat . ', ' . $departure->lon . ')';
		}

		foreach($this->waypoints as $data) {
			if($data instanceof Waypoint)
				$positions[] = 'new google.maps.LatLng(' . $data->lat . ', ' . $data->lon . ')';
		}

		if(!is_null($arrival)) {
			$positions[] = 'new google.maps.LatLng(' . $arrival->lat . ', ' . $arrival->lon . ')';
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
		return $ident;
		//return (str_contains($ident, '/')) ? explode('/', $ident)[0] : $ident;
	}
	
	protected function airways($waypoints) {
		return Airway::whereIn('airway', $waypoints)->orderBy('seq')->get()->groupBy('airway');
	}

	protected function depapps($sid, $star, $departure, $arrival) {
		return DepApp::where(function($dep) use ($sid, $departure) {
			$dep->whereIdent($sid);
			$dep->whereType('D');
			if(!is_null($departure)) $dep->whereAirportId($departure);
		})->orWhere(function($arr) use ($star, $arrival) {
			$arr->whereIdent($star);
			$arr->whereType('A');
			if(!is_null($arrival)) $arr->whereAirportId($arrival);
		})->lists('route','ident');
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
		
		return Waypoint::whereIn('ident', $in_clause)->get()->groupBy('ident');
		
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

	protected function shortCoordinate($ident) {
		$shortCoord = preg_match('/^(?:(?:(\d{2})([N|E|S|W])(\d{2}))|(?:(\d{2})(\d{2})([N|E|S|W])))$/', $ident, $matches);

		if(!$shortCoord) {
			return $ident;
		}

		$matches = array_values(array_filter($matches));

		$lat = $matches[1];
		if(is_numeric($matches[2])) {
			$lon = $matches[2];
			$dir = $matches[3];
		} else {
			$lon = $matches[2] + 100;
			$dir = $matches[3];
		}

		switch($dir) {
			case 'N':
				return $lat . 'N' . $lon . 'W';
			case 'E':
				return $lat . 'N' . $lon . 'E';
			case 'S':
				return $lat . 'S' . $lon . 'W';
			case 'W':
				return $lat . 'S' . $lon . 'E';
		}
	}
}