<?php

$build = substr(File::get(base_path() . '/.git/' . trim(substr(File::get(base_path() . '/.git/HEAD'), 5))),0,7);
View::share('build', $build);

function flag($code) {
	return asset('assets/images/flags/' . $code . '.png');
}