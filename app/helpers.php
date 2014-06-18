<?php

function flag($code) {
	return asset('assets/images/flags/' . $code . '.png');
}

function piechartData($data) {
	$colours = ['#48CA3B', '#00BCE1', '#4D3A7D', '#AD1D28', '#DEBB27', '#DF6E1E'];
	shuffle($colours);
	$colours[] = '#111111';
	$colourCount = count($colours)+1;
	
	$results = array();
	$colourResults = array();

	foreach($data as $key => $entry) {
		$results[] = '{ label: "' . $entry[0] . '",  data: ' . $entry[1] . ', color: "' . (isset($entry[2]) ? $entry[2] : $colours[$key%$colourCount]) . '"}';
		$colourResults[$entry[0]] = (isset($entry[2]) ? $entry[2] : $colours[$key%$colourCount]);
	}

	return array('javascript' => implode(",\n", $results), 'colours' => $colourResults);
}

function altitudeColour($altitude, $implode = false, $hex = false) {
	if($altitude < 0) $altitude = 0;
	$steps = $altitude / 40;
	$stage = floor($steps / 255);
	$remainder = $steps % 255;

	switch($stage) {
		case 0: // < 10200 ft
			$rgb = [0, 255, $remainder];
			break;
		case 1: // 10200 - 20400 ft
			$rgb = [0, 255-$remainder, 255];
			break;
		case 2: // 20400 - 30600 ft
			$rgb = [$remainder, 0, 255];
			break;
		case 3: // 30600 - 40800 ft
			$rgb = [255, 0, 255-$remainder];
			break;
		default: // 40800 - 51000 ft
			$rgb = [255, 0, 0];
			break;
	}

	if($hex) {
		foreach($rgb as &$colour) {
			$colour = str_pad(dechex($colour), 2, "0", STR_PAD_LEFT);
		}
	}
	
	if($implode !== false)
		return implode($implode, $rgb);

	return $rgb;
}

function fscanfe($handle, $format, Closure $callback) {
	$filename = stream_get_meta_data($handle)['uri'];
	$filesize = filesize($filename);

	do {
		$lineinfo = fscanf($handle, $format);
		call_user_func_array($callback, array($lineinfo));
	} while(ftell($handle) != $filesize);
}

function progressiveInsert($table, $data) {
	if(is_scalar($table)) $model = DB::table($table);
	else {
		$model = $table;
		$table = get_class($model);
	}

	$remaining = count($data);
	$step = 0;
	do {
		try {
			$model->insert(array_slice($data, 100 * $step, 100));
		} catch(Exception $e) {
			Log::error($e);
		}
		$remaining -= 100;
		$step++;
	} while($remaining > 0);

	unset($remaining, $data, $step);
}

function sentenceSplitter($str, $length, $offset = 0, $default = null) {
	$words = explode(' ', $str);

	$current = 0;
	$output = '';

	foreach($words as $word) {
		$wordLength = strlen($word) + 1;

		if($current + $wordLength <= $offset) {
			$current += $wordLength;
			continue;
		} elseif(strlen($output) + $wordLength <= $length) {
			if(($current + $wordLength) >= $offset)
				$output .= $word . ' ';

			$current += $wordLength;
		} else {
			break;
		}
	}

	return (strlen($output) > 0) ? trim($output) : $default;
}

View::composer('layouts.admin', function($view) {
	if(!is_null($team = Auth::user()->team)) {
		$user = array(
			'name' => $team->name,
			'firstname' => !empty($team->firstname) ? $team->firstname : explode(' ', $team->name)[0],
			'job' => $team->job,
			'photo' => $team->photo
		);
	} else {
		$team = Auth::user();
		$user = array(
			'name' => $team->name,
			'firstname' => explode(' ', $team->name)[0],
			'job' => '',
			'photo' => false
		);
	}
	$view->with('user', $user);
});

View::composer(['layouts.master', 'layouts.errors'], function($view) {
	$view->with('build', substr(File::get(base_path() . '/.git/' . trim(substr(File::get(base_path() . '/.git/HEAD'), 5))),0,7));
	$view->with('statsPilots', Cache::get('vatsim.pilots'));
	$view->with('statsAtc', Cache::get('vatsim.atc'));
});

View::composer(['layouts.master','flight.show','atc.show'], function($view) {
	$view->with('mapstyle', Auth::guest() || is_null(Auth::user()->map) ? 'blue' : Auth::user()->map);
});

View::composer('admin._partials.sidebar', function($view) {
	$view->with('airlineRequestCount', count(AirlineChange::groupBy('airline_id')->remember(1)->lists('airline_id')));
	$view->with('airportRequestCount', count(AirportChange::groupBy('airport_id')->remember(1)->lists('airport_id')));
});

View::composer('admin._partials.tasks', function($view) {
	if(!is_null($team = Auth::user()->team) && !is_null($team->jira)) {
		$tasks = JiraIssue::where('assignee', $team->jira)->where('resolution','Unresolved')->orderBy('priority','desc')->orderBy('updatedDate', 'DESC')->get();
	} else {
		$tasks = false;
	}

	$view->with('tasks', $tasks); 
});
