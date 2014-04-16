<?php

$build = substr(File::get(base_path() . '/.git/' . trim(substr(File::get(base_path() . '/.git/HEAD'), 5))),0,7);
View::share('build', $build);

function flag($code) {
	return asset('assets/images/flags/' . $code . '.png');
}

function piechartData($data) {
	$colours = ['#48CA3B', '#00BCE1', '#4D3A7D', '#AD1D28', '#DEBB27', '#DF6E1E'];
	shuffle($colours);
	$colours[] = '#111111';
	$colourCount = count($colours)+1;
	
	$results = array();

	foreach($data as $key => $entry) {
		$results[] = '{ label: "' . $entry[0] . '",  data: ' . $entry[1] . ', color: "' . (isset($entry[2]) ? $entry[2] : $colours[$key%$colourCount]) . '"}';
	}

	return implode(",\n", $results);
}