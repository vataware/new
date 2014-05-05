<?php

class DatafeedClean {
	
	function fire($job, $data = null) {

		$job->delete();

		$flights = Flight::whereMissing(true)->with('lastPosition')->take(100)->get();

		foreach($flights as $flight) {
			if(Carbon::now()->diffInMinutes($flight->lastPosition->updated_at) >= 60) {
				$flight->delete();
				Log::info('queue:datafeed[' . $job->getJobId() . '] deleted flight #' . $flight->id);
			}
			unset($flight);
		}

	}

}