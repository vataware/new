<?php

use Vataware\VatsimAuth\SSO;

class AuthController extends BaseController {
	
	function login() {
		$SSO = new SSO(
			Config::get('vatsim.base'),
			Config::get('vatsim.key'),
			Config::get('vatsim.secret'),
			Config::get('vatsim.method'),
			Config::get('vatsim.cert')
		);

		$token = $SSO->requestToken(Config::get('vatsim.return'));

		if($token) {
			Session::put('vatsimauth', array(
				'key' => (string) $token->token->oauth_token,
				'secret' => (string)$token->token->oauth_token_secret
			));

			return $SSO->sendToVatsim();
		} else {
			$error = $SSO->error();
			throw new AuthException($error['message']);
		}
	}

	function logout() {
		Auth::logout();
		Messages::success('Thanks for flying <strong>vataware</strong>! Hope to see you back on board soon.');
		return Redirect::home();
	}

	function validate() {
		if(!Session::has('vatsimauth')) {
			throw new AuthException('Session does not exist');
		}

		$SSO = new SSO(
			Config::get('vatsim.base'),
			Config::get('vatsim.key'),
			Config::get('vatsim.secret'),
			Config::get('vatsim.method'),
			Config::get('vatsim.cert')
		);

		$session = Session::get('vatsimauth');
		
		if(Input::get('oauth_token') !== $session['key']) {
			throw new AuthException('Returned token does not match');
			return;
		}

		if(!Input::has('oauth_verifier')) {
			throw new AuthException('No verification code provided');
		}

		$user = $SSO->checkLogin($session['key'], $session['secret'], Input::get('oauth_verifier'));

		if($user) {
			Session::forget('vatsimauth');

			$authUser = User::find($user->user->id);
			if(is_null($authUser)) {
				$authUser = new User;
				$authUser->vatsim_id = $user->user->id;
				$authUser->name = trim($user->user->name_first . ' ' . $user->user->name_last);
			}
			$authUser->last_login = Carbon::now();
			$authUser->save();
			
			Auth::login($authUser);
			
			Messages::success('Welcome on board, <strong>' . $authUser->name . '</strong>!');

			return Redirect::intended('/');
		} else {
			$error = $SSO->error();
			throw new AuthException($error['message']);
		}
	}

	function intend() {
		if(Auth::check()) {
			return Redirect::to(Input::get('vataware_callback'));
		} else {
			return Redirect::guest(URL::route('user.login'));
		}
	}

}