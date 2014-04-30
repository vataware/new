<?php

class FlightStat {
	
	private $_query;

	private $_total;

	function __construct($query) {
		$this->_query = $query;
		$this->_total = $query->count();
	}

	function distances($km) {
		$nm = $km * 0.54;
		$mi = $km * 0.6214;
		$ae = $km / 40075;

		return compact('nm', 'mi', 'km', 'ae');
	}

	function durations() {
		$times = $this->query()->orderBy('duration')->whereIn('state',array(2,3))->get();

		$shortest = $times->first();
		$shortest->hours = floor($shortest->duration/60);
		$shortest->minutes = str_pad(($shortest->duration - ($shortest->hours * 60)),2,'0',STR_PAD_LEFT);

		$longest = $times->last();
		$longest->hours = floor($longest->duration/60);
		$longest->minutes = str_pad(($longest->duration - ($longest->hours * 60)),2,'0',STR_PAD_LEFT);

		$total = $this->query()->sum('duration');
		$hours = floor($total/60);
		$minutes = str_pad(($total - ($hours * 60)),2,'0',STR_PAD_LEFT);

		return compact('shortest','longest','hours','minutes');
	}

	function citypair() {
		$citypairs = $this->query()->select(DB::raw('IF(departure_id < arrival_id,CONCAT(departure_id,\'-\',arrival_id),CONCAT(arrival_id,\'-\',departure_id)) AS citypair'))->where('departure_id','!=','')->where('arrival_id','!=','')->lists('citypair');
		$citypairsCounter = array_count_values($citypairs);
		arsort($citypairsCounter);

		$route = key($citypairsCounter);
		$count = head($citypairsCounter);
		$airports = explode('-', key($citypairsCounter));
		$airportsData = Airport::with('country')->whereIn('id',$airports)->get();

		return ['code' => $airports, 'data' => $airportsData];
	}

	function topAirlines() {
		$other = 0;
		$result = array();
		$chart = array();
		$names = array();

		$counter = $this->query()->select(DB::raw('airline_id, count(airline_id) as counter'))->groupBy('airline_id')->orderBy('counter','DESC')->whereCallsignType(1)->lists('counter','airline_id');
		if(count($counter) > 0) {
			$namesRaw = Airline::whereIn('icao',array_keys($counter))->get();
			foreach($namesRaw as $airline) {
				$names[$airline->icao] = $airline; 
			}
			
			foreach($counter as $key => $flights) {
				if(count($result) < 5 && array_key_exists($key, $names)) {
					$percentage = ($this->_total == 0) ? 0 : number_format($flights / $this->_total * 100, 1);
					$result[] = array('data' => $names[$key], 'count' => $flights, 'percent' => $percentage);
					if($percentage > 0) $chart[] = [$names[$key]->icao, $percentage];
				} else
					$other += $flights;
			}
		}

		$private = $this->query()->whereCallsignType(2)->count();
		$unknown = $this->query()->whereCallsignType(0)->count();
		$result['Private'] = array('count' => $private, 'percent' => ($this->_total == 0) ? 0 : number_format($private / $this->_total * 100,1));
		if($result['Private']['percent'] > 0) $chart[] = ['Private', $result['Private']['percent']];
		$result['Other'] = array('count' => $other + $unknown, 'percent' => ($this->_total == 0) ? 0 : number_format(($other + $unknown) / $this->_total * 100,1));
		if($result['Other']['percent'] > 0) $chart[] = ['Other', $result['Other']['percent']];

		return array('table' => $result, 'chart' => piechartData($chart));
	}

	function topAircraft() {
		$other = 0;
		$result = array();
		$names = array();
		$chart = array();

		$counter = $this->query()->select(DB::raw('aircraft_id, count(aircraft_id) as counter'))->groupBy('aircraft_id')->where('aircraft_id','!=','')->whereNotNull('aircraft_id')->orderBy('counter','DESC')->lists('counter','aircraft_id');
		if(count($counter) > 0) {
			$namesRaw = Aircraft::whereIn('code',array_keys($counter))->get();
			foreach($namesRaw as $aircraft) {
				$names[$aircraft->code][] = $aircraft; 
			}
			$other = $this->_total - array_sum($counter);
		}

		foreach($counter as $key => $flights) {
			if(count($result) < 5 && array_key_exists($key, $names)) {
				$percentage = ($this->_total == 0) ? 0 : number_format($flights / $this->_total * 100, 1);
				$result[] = array('data' => $names[$key], 'count' => $flights, 'percent' => $percentage);
				if($percentage > 0) $chart[] = [$key, $percentage];
			} else
				$other += $flights;
		}

		$result['Other'] = array('count' => $other, 'percent' => ($this->_total == 0) ? 0 : number_format($other / $this->_total * 100,1));
		if($result['Other']['percent'] > 0) $chart[] = ['Other', $result['Other']['percent']];

		return array('table' => $result, 'chart' => piechartData($chart));
	}

	function topAirports() {
		$other = 0;
		$result = array();
		$chart = array();
		$names = array();		

		$origCounter = $this->query()->select(DB::raw('departure_id, count(departure_id) as counter'))->groupBy('departure_id')->where('departure_id','!=','')->orderBy('counter','DESC')->lists('counter','departure_id');
		$destCounter = $this->query()->select(DB::raw('arrival_id, count(arrival_id) as counter'))->groupBy('arrival_id')->where('arrival_id','!=','')->orderBy('counter','DESC')->lists('counter','arrival_id');
		$airportsId = array_unique(array_merge(array_keys($origCounter),array_keys($destCounter)));
		$counter = array_combine($airportsId, $airportsId);
		foreach($counter as &$airportId) {
			$airportId = @$origCounter[$airportId] + @$destCounter[$airportId];
		}
		arsort($counter);
		if(count($counter) > 0) {
			$namesRaw = Airport::with('country')->whereIn('icao',array_keys($counter))->get();
			foreach($namesRaw as $airport) {
				$names[$airport->icao] = $airport; 
			}
		}
		
		foreach($counter as $key => $flights) {
			if(count($result) < 5 && array_key_exists($key, $names)) {
				$percentage = ($this->_total == 0) ? 0 : number_format($flights / ($this->_total*2) * 100, 1);
				$result[] = array('data' => $names[$key], 'count' => $flights, 'percent' => $percentage);
				if($percentage > 0) $chart[] = [$names[$key]->icao, $percentage];
			} else
				$other += $flights;
		}

		$result['Other'] = array('count' => $other, 'percent' => ($this->_total == 0) ? 0 : number_format($other / ($this->_total * 2) * 100,1));
		if($result['Other']['percent'] > 0) $chart[] = ['Other', $result['Other']['percent']];

		return array('table' => $result, 'chart' => piechartData($chart));
	}

	private function query() {
		return clone $this->_query;
	}

}