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

Route::get('', ['as' => 'home', 'uses' => 'HomeController@index']);
Route::get('flights', ['as' => 'flights', 'uses' => 'HomeController@flights']);
Route::get('flight/{flight}', ['as' => 'flight', 'uses' => 'HomeController@flight'])->where('flight','[0-9]+');

Route::bind('flight',function($value, $route) {
	$flight = Flight::with('aircraft','departure','arrival','pilot','departureCountry','arrivalCountry','airline','positions')->find($value);

	if(is_null($flight)) {
		return App::abort(404);
	} else {
		return $flight;
	}
});

// Redirect old urls
Route::get('flight.cfm', function() {
	if(!Input::has('id')) return Redirect::route('flights');
	return Redirect::route('flight', array('flight' => Input::get('id')), 301);
});
