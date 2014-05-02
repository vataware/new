<?php

class ControllerStat {
	
	private $_query;

	private $_total;

	function __construct($query) {
		$this->_query = $query;
		$this->_total = $query->count();
	}

	function durations($total) {
		$hours = floor($total/60);
		$minutes = str_pad(($total - ($hours * 60)),2,'0',STR_PAD_LEFT);

		return compact('hours','minutes');
	}

	function airport() {
		return $this->query()->with('airport','airport.country')->whereNotNull('airport_id')
			->select(DB::raw('airport_id, count(airport_id) as counter'))->groupBy('airport_id')->orderBy('counter','DESC')->first()->airport;
	}

	function topAirports() {
		$other = 0;
		$result = array();
		$chart = array();
		$names = array();

		$counter = $this->query()->select(DB::raw('airport_id, count(airport_id) as counter'))->groupBy('airport_id')->whereNotNull('airport_id')->orderBy('counter','DESC')->lists('counter','airport_id');
		$other = $this->_total - array_sum($counter);
		if(count($counter) > 0) {
			$namesRaw = Airport::with('country')->whereIn('icao',array_keys($counter))->get();
			foreach($namesRaw as $airport) {
				$names[$airport->icao] = $airport; 
			}

			foreach($counter as $key => $flights) {
				if(count($result) < 5 && array_key_exists($key, $names)) {
					$percentage = ($this->_total == 0) ? 0 : number_format($flights / $this->_total * 100, 1);
					$result[] = array('data' => $names[$key], 'count' => $flights, 'percent' => $percentage, 'key' => $key);
					if($percentage > 0) $chart[] = [$key, $percentage];
				} else
					$other += $flights;
			}
		}

		$result['Other'] = array('count' => $other, 'percent' => ($this->_total == 0) ? 0 : number_format($other / $this->_total * 100,1), 'key' => 'Other');
		$chart[] = ['Other', $result['Other']['percent']];

		$piechartData = piechartData($chart);

		return array('table' => $result, 'chart' => $piechartData['javascript'], 'colours' => $piechartData['colours']);
	}

	function topFacilities() {
		$other = 0;
		$result = array();
		$chart = array();

		$counter = $this->query()->select(DB::raw('facility_id, count(facility_id) as counter'))->groupBy('facility_id')->whereNotIn('facility_id', array(99))->orderBy('counter','DESC')->get();
		if($counter->count() > 0) {
			foreach($counter as $flights) {
				if(count($result) < 5) {
					$percentage = ($this->_total == 0) ? 0 : number_format($flights->counter / $this->_total * 100, 1);
					$result[] = array('data' => $flights->facility, 'count' => $flights->counter, 'percent' => $percentage, 'key' => $flights->facilityAbbr);
					if($percentage > 0) $chart[] = [$flights->facilityAbbr, $percentage];
				} else
					$other += $flights->counter;
			}
		}

		$result['Other'] = array('count' => $other, 'percent' => ($this->_total == 0) ? 0 : number_format($other / $this->_total * 100,1), 'key' => 'Other');
		$chart[] = ['Other', $result['Other']['percent']];

		$piechartData = piechartData($chart);

		return array('table' => $result, 'chart' => $piechartData['javascript'], 'colours' => $piechartData['colours']);
	}

	private function query() {
		return clone $this->_query;
	}

}