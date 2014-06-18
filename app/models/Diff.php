<?php

class Diff {

	static function compare($model, array $input, Closure $callback, array $columns = array(), $merge = true) {
		if($merge) $columns = array_merge($columns, array_keys($model->toArray()));
		$changed = array();
		
		foreach(array_only($input, $columns) as $key => $value) {
			if(trim($value) != trim($model->getOriginal($key)) && strlen(trim($value)) > 0) {
				$changed[] = $key;
				$value = trim($value);
				call_user_func_array($callback, array($key, $value, $model));
			}
		}

		return $changed;
	}

}