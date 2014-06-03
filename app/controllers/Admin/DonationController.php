<?php namespace Admin;

use BaseController, Donation, Gateway;

class DonationController extends BaseController {
	
	protected $layout = 'layouts.admin';

	function index() {
		$donations = Donation::get();
		$gateways = Gateway::get();

		$this->autoRender(compact('donations','gateways'), 'Supporting Vataware');
	}

	function create() {

	}

	function store() {

	}

	function edit(Donation $donation) {

	}

	function update(Donation $donation) {

	}

	function destroy(Donation $donation) {

	}

	function gatewayCreate() {

	}

	function gatewayStore() {

	}

	function gatewayEdit(Gateway $gateway) {

	}

	function gatewayUpdate(Gateway $gateway) {

	}

	function gatewayDestroy(Gateway $gateway) {

	}
}