<?php

class DbConfig {
	
	protected static $_configs = null;

	private static function load() {
		return self::$_configs = array_map(function($value) {
			return unserialize($value);
		}, DB::table('config')->lists('value','key'));
	}

	static function all() {
		if(is_null(self::$_configs))
			self::load();

		if(is_null(self::$_configs))
			return array();
		else
			return self::$_configs;
	}

	static function get($key, $default = null) {
		if(is_null(self::$_configs))
			self::load();

		if(array_key_exists($key, self::$_configs))
			return self::$_configs[$key];
		else
			return $default;
	}

	static function has($key) {
		if(is_null(self::$_configs))
			self::load();

		return array_key_exists($key, self::$_configs);
	}

	static function put($key, $value) {
		$affected = DB::table('config')->where('key', $key)->update(array('value' => serialize($value)));
		if($affected == 0) {
			DB::table('config')->insert(array('key' => $key, 'value' => serialize($value)));
		}
		return true;
	}

}