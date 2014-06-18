<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	Messages::store();
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest(URL::route('user.login'));
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

Route::filter('flatten.flight', function($route, $request, $response) {
	$flight = $route->getParameter('flight');
	if($flight->state == 2 && $flight->processed && $flight->pilot->processing == 1) {
		Flatten::end($response);
	}
});

Route::filter('flatten.atc', function($route, $request, $response) {
	$atc = $route->getParameter('atc');
	if(!is_null($atc->end) && $atc->processed && $atc->pilot->processing == 1) {
		Flatten::end($response);
	}
});

Route::filter('admin', function() {
	if(!Auth::user()->isAdmin()) {
		$timeline = new Timeline;
		$timeline->type = 'unauthorised-access';
		$timeline->user_id = Auth::id();
		$timeline->activity = array('name' => Auth::user()->name);
		$timeline->save();
		return App::abort(404);
	}
});