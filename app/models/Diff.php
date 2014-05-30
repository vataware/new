<?php

class Diff {

	static function compare($model, array $input, Closure $callback) {
		$columns = array_keys($model->toArray());
		$changed = array();

		foreach(array_only($input, $columns) as $key => $value) {
			if(trim($value) != trim($model->getOriginal($key))) {
				$changed[] = $key;
				$value = trim($value);
				call_user_func_array($callback, array($key, $value, $model));
			}
		}

		return $changed;
	}

}