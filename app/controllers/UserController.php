<?php

class UserController extends BaseController {
	
	protected $layout = 'layouts.master';

	function edit() {
		$user = Auth::user();

		$this->autoRender(compact('user'), 'My Account');
	}

	function update() {
		$rules = array(
			'anonymous' => 'in:0,1'
		);

		$validator = Validator::make(Input::all(), $rules);

		if($validator->fails()) {
			Messages::error($validator->messages()->all());
			return Redirect::back()->withInput();
		}

		$user = Auth::user();
		$user->anonymous = Input::get('anonymous');
		$user->save();

		Messages::success('Your account has been updated.');
		return Redirect::back();
	}

	function name() {
		$user = Auth::user();

		$original = $user->name;

		$it = new XmlIterator\XmlIterator('https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=' . $user->vatsim_id, 'user');
		$official = iterator_to_array($it)[0];
		
		$user->name = (string) $official['name_first'] . ' ' . (string) $official['name_last'];
		$user->save();

		Messages::success('Your name has been updated from <strong>' . $original . '</strong> to <strong>' . $user->name . '</strong>');
		return Redirect::route('user.edit');
	}

	function processing() {
		$user = Auth::user();

		if($user->processing == 2) {
			$user->processing = 0;
			$user->save();

			Messages::success('The processing has been reset. Visit your pilot or controller profile to start processing again');
			return Redirect::route('user.edit');
		} else {
			Messages::error('You are not allowed to perform this action.');
			return Redirect::route('user.edit');
		}

	}

}