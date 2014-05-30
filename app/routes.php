<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('',						['as' => 'home',				'uses' => 'HomeController@index']);
Route::get('team',					['as' => 'team',				'uses' => 'HomeController@team']);
Route::get('donations',				['as' => 'donations',			'uses' => 'HomeController@donations']);
Route::get('map/api',				['as' => 'map.api',				'uses' => 'HomeController@mapApi']);
Route::get('map/flight',			['as' => 'map.flight',			'uses' => 'HomeController@mapFlight']);


// User
Route::group(['prefix' => 'user'], function() {
	Route::get('login',				['as' => 'user.login', 			'uses' => 'AuthController@login', 'before' => 'guest']);
	Route::get('logout',			['as' => 'user.logout',			'uses' => 'AuthController@logout', 'before' => 'auth']);
	Route::get('validate',			['as' => 'user.validate',		'uses' => 'AuthController@validate', 'before' => 'guest']);
	Route::get('intend', 			['as' => 'user.intend',			'uses' => 'AuthController@intend']);

	Route::get('',					['as' => 'user.edit',			'uses' => 'UserController@edit', 'before' => 'auth']);
	Route::put('',					['as' => 'user.update',			'uses' => 'UserController@update', 'before' => 'auth']);
	Route::get('name',				['as' => 'user.name',			'uses' => 'UserController@name', 'before' => 'auth']);
	Route::get('processing',		['as' => 'user.processing',		'uses' => 'UserController@processing', 'before' => 'auth']);
});

// Flights
Route::group(['prefix' => 'flight'], function() {
	Route::get('',					['as' => 'flight.index',		'uses' => 'FlightController@index']);
	Route::get('{flight}', 			['as' => 'flight.show',			'uses' => 'FlightController@show'])
		->where('flight','[0-9]+')
		->after('flatten.flight');
});

// ATC
Route::group(['prefix' => 'atc'], function() {
	Route::get('',					['as' => 'atc.index',			'uses' => 'ATCController@index']);
	Route::get('{atc}',				['as' => 'atc.show',			'uses' => 'ATCController@show'])
		->where('atc','[0-9]+')
		->after('flatten.atc');
});

// Pilots
Route::group(['prefix' => 'pilot'], function() {
	Route::get('',					['as' => 'pilot.index',			'uses' => 'PilotController@index']);
	Route::get('{pilot}',			['as' => 'pilot.show',			'uses' => 'PilotController@show'])
		->where('pilot','[0-9]+');
	Route::get('{pilot}/flights',	['as' => 'pilot.flights',		'uses' => 'PilotController@flights'])
		->where('pilot','[0-9]+');
});

// Controllers
Route::group(['prefix' => 'controller'], function() {
	Route::get('',					['as' => 'controller.index',	'uses' => 'ControllerController@index']);
	Route::get('{pilot}',			['as' => 'controller.show',		'uses' => 'ControllerController@show'])
		->where('pilot','[0-9]+');
	// Route::get('{pilot}/duties',	['as' => 'controller.duties',	'uses' => 'ControllerController@duties'])
	//	->where('pilot','[0-9]+');
});

Route::pattern('airport','[A-Z0-9]{3,4}');
Route::group(['prefix' => 'airport'], function() {
	Route::get('',					['as' => 'airport.index',		'uses' => 'AirportController@index']);
	Route::get('{airport}',			['as' => 'airport.show',		'uses' => 'AirportController@show']);
	Route::get('{airport}/edit',	['as' => 'airport.edit',		'uses' => 'AirportController@edit']);
	Route::put('{airport}',			['as' => 'airport.update',		'uses' => 'AirportController@update']);
});

Route::pattern('airline','[A-Z0-9]+');
Route::group(['prefix' => 'airline'], function() {
	Route::get('',					['as' => 'airline.index',		'uses' => 'AirlineController@index']);
	Route::get('{airline}',			['as' => 'airline.show',		'uses' => 'AirlineController@show']);
	Route::get('{airline}/edit',	['as' => 'airline.edit',		'uses' => 'AirlineController@edit']);
	Route::put('{airline}',			['as' => 'airline.update',		'uses' => 'AirlineController@update']);
});

Route::get('search',				['as' => 'search',				'uses' => 'SearchController@index']);

Route::get('citypair/{departure}-{arrival}',['as' => 'citypair',	'uses' => 'FlightController@citypair'])
	->where('departure','[A-Z0-9]{3,4}')
	->where('arrival','[A-Z0-9]{3,4}');

/*
|--------------------------------------------------------------------------
| Bindings
|--------------------------------------------------------------------------
|
| Bindings replace the variables in the URL with their respective classes
| that are passed as a parameter to the controller methods.
|
*/

Route::bind('flight',function($value, $route) {
	$flight = Flight::with(['aircraft','departure','arrival','pilot','departureCountry','arrivalCountry','airline','positions' => function($positions) {
		$positions->join('updates','positions.update_id','=','updates.id');
		$positions->select('positions.*', DB::raw('updates.timestamp AS time'));
		$positions->orderBy('time','asc');
	}])->find($value);

	if(is_null($flight) || $value == 0) {
		return App::abort(404);
	} else {
		return $flight;
	}
});

Route::bind('atc',function($value, $route) {
	$atc = ATC::with('pilot')->find($value);

	if(is_null($atc) || $value == 0) {
		return App::abort(404);
	} else {
		return $atc;
	}
});

Route::bind('pilot',function($value, $route) {
	$pilot = Pilot::whereVatsimId($value)->first();

	if(is_null($pilot) || $value == 0)
		return App::abort(404);
	else
		return $pilot;
});

Route::bind('airport',function($value, $route) {
	$airport = Airport::whereIcao($value)->first();

	if(is_null($airport))
		return App::abort(404);
	else
		return $airport;
});

Route::bind('airline',function($value, $route) {
	$airline = Airline::whereIcao($value)->first();

	if(is_null($airline))
		return App::abort(404);
	else
		return $airline;
});

/*
|--------------------------------------------------------------------------
| Legacy URLs
|--------------------------------------------------------------------------
|
| These are the old URLs for original vataware. When possible it should be
| a 301 (permanent redirect). This is not always possible though.
|
*/

Route::get('index.cfm', function() {
	return Redirect::route('home', array(), 301);
});

Route::get('flight.cfm', function() {
	if(!Input::has('id')) return Redirect::route('flight.index');
	return Redirect::route('flight.show', array('flight' => Input::get('id')), 301);
});

Route::get('pilot.cfm', function() {
	if(!Input::has('cid')) return Redirect::route('pilot.index');
	return Redirect::route('pilot.show', array('pilot' => Input::get('cid')), 301);
});

Route::get('airport.cfm', function() {
	if(!Input::has('airport')) return Redirect::route('airport.index');
	return Redirect::route('airport.show', array('airport' => strtoupper(Input::get('airport'))), 301);
});

Route::get('airline.cfm', function() {
	if(!Input::has('icao')) return Redirect::route('airline.index');
	return Redirect::route('airline.show', array('airline' => strtoupper(Input::get('icao'))), 301);
});

Route::get('citypair.cfm', function() {
	if(!Input::has('from') || !Input::has('to')) return App::abort(404);
	return Redirect::route('citypair', array('departure' => strtoupper(Input::get('from')), 'arrival' => strtoupper(Input::get('to'))), 301);
});

Route::get('routes.cfm', function() {
	if(!Input::has('from') || !Input::has('to')) return App::abort(404);
	return Redirect::route('citypair', array('departure' => strtoupper(Input::get('from')), 'arrival' => strtoupper(Input::get('to'))), 301);
});

Route::get('pilotredir.cfm', function() {
	if(!Input::has('cid')) return Redirect::route('pilot.index');

	$latestFlight = Flight::whereVatsimId(Input::get('cid'))->orderBy('id','desc')->first();

	// Redirect to pilot page is last flight cannot be found
	if(is_null($latestFlight)) return Redirect::route('pilot.show', array('pilot' => Input::get('cid')));

	// Redirect to flight page when flight is found
	return Redirect::route('flight.show', array('flight' => $latestFlight->id));
});

/*
|--------------------------------------------------------------------------
| Push Queue Handler
|--------------------------------------------------------------------------
|
| The route that received the jobs from the push queue should be here.
|
*/

Route::post('queue/receive/1q2w3e4r5t6y7u8i9o0p', function() {
	return Queue::marshal();
});