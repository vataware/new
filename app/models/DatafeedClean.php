<?php

class DatafeedClean {
	
	function fire($job, $data = null) {

		$job->delete();

		$flights = Flight::whereMissing(true)->select('flights.id','updates.timestamp')->join('positions','flights.id','=','positions.flight_id')->join('updates','positions.update_id','=','updates.id')->take(10000)->get();

		foreach($flights as $flight) {
			if(Carbon::now()->diffInMinutes(Carbon::parse($flight->timestamp)) >= 60) {
				$flight->delete();
				// Log::info('queue:datafeed[' . $job->getJobId() . '] deleted flight #' . $flight->id . ', missing: ' . Carbon::now()->diffInMinutes(Carbon::parse($flight->timestamp)));
			}
			unset($flight);
		}

	}

}