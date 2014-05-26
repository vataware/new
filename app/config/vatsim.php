<?php

return array(
	
	/*
	 * The location of the VATSIM OAuth interface
	 */
	'base' => '',

	/*
	 * The consumer key for your organisation (provided by VATSIM)
	 */
	'key' => $_ENV['OAUTH_KEY'],

	 /*
	 * The secret key for your orgnisation (provided by VATSIM)
	 * Do not give this to anyone else or display it to your users. It must be kept server-side
	 */
	'secret' => $_ENV['OAUTH_SECRET'],

	/*
	 * The URL users will be redirected to after they log in, this should
	 * be on the same server as the request
	 */
	'return' => URL::route('user.validate'),

	/*
	 * The signing method you are using to encrypt your request signature.
	 * Different options must be enabled on your account at VATSIM.
	 * Options: RSA / HMAC
	 */
	'method' => $_ENV['OAUTH_METHOD'],

	/*
	 * Your RSA **PRIVATE** key
	 * If you are not using RSA, this value can be anything (or not set)
	 */
	'cert' => $_ENV['OAUTH_CERT']

);