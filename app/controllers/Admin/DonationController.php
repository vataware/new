<?php namespace Admin;

use BaseController, Donation, Gateway, Input, Redirect, Messages, Validator;

class DonationController extends BaseController {
	
	protected $layout = 'layouts.admin';

	function index() {
		$donations = Donation::get();
		$gateways = Gateway::get();

		$this->autoRender(compact('donations','gateways'), 'Supporting Vataware');
	}

	function create() {
		return $this->autoRender();
	}

	function store() {
		$rules = array(
			'name' => 'required',
			'amount' => 'required|numeric|min:0',
			'vatsim_id' => 'integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::route('admin.donation.index');
		}

		$donation = new Donation;
		$donation->name = Input::get('name');
		$donation->amount = Input::get('amount');
		$donation->vatsim_id = Input::get('vatsim_id') ?: null;
		$donation->save();

		Messages::success('Donation by <strong>' . $donation->name . '</strong> for an amount of <strong>$' . $donation->amount . ' has been added.');
		return Redirect::route('admin.donation.index');
	}

	function edit(Donation $donation) {
		return $this->autoRender(compact('donation'));
	}

	function update(Donation $donation) {
		$rules = array(
			'name' => 'required',
			'amount' => 'required|numeric|min:0',
			'vatsim_id' => 'integer',
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::route('admin.donation.index');
		}

		$donation->name = Input::get('name');
		$donation->amount = Input::get('amount');
		$donation->vatsim_id = Input::get('vatsim_id') ?: null;
		$donation->save();

		Messages::success('Donation by <strong>' . $donation->name . '</strong> has been updated.');
		return Redirect::route('admin.donation.index');
	}

	function destroy(Donation $donation) {

	}

	function gatewayCreate() {
		return $this->autoRender();
	}

	function gatewayStore() {
		$rules = array(
			'name' => 'required',
			'link' => 'required|url',
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::route('admin.donation.index');
		}

		$gateway = new Gateway;
		$gateway->name = Input::get('name');
		$gateway->link = Input::get('link');
		$gateway->note = Input::get('note') ?: null;
		$gateway->save();

		Messages::success('Gateway <strong>' . $gateway->name . '</strong> has been added.');
		return Redirect::route('admin.donation.index');
	}

	function gatewayEdit(Gateway $gateway) {
		return $this->autoRender(compact('gateway'));
	}

	function gatewayUpdate(Gateway $gateway) {
		$rules = array(
			'name' => 'required',
			'link' => 'required|url',
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::route('admin.donation.index');
		}

		$gateway->name = Input::get('name');
		$gateway->link = Input::get('link');
		$gateway->note = Input::get('note') ?: null;
		$gateway->save();

		Messages::success('Gateway <strong>' . $gateway->name . '</strong> has been updated.');
		return Redirect::route('admin.donation.index');
	}

	function gatewayDestroy(Gateway $gateway) {

	}
}