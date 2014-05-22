<?php namespace Vataware\IronCache;

use Illuminate\Cache\StoreInterface;
use \IronCache;

class IronCacheStore implements StoreInterface {

	private $ironcache;

	private $prefix;

	public function __construct($prefix = '', array $config, $app)
	{
		$name = $config['name'];
		if(is_scalar($config['auth']))
			$config = array_only($app['config']->get($config['auth']), ['token','project']);

		$this->ironcache = new IronCache(array(
			'token'         =>  $config['token'],
			'project_id'    =>  $config['project']
		));

		$this->ironcache->setCacheName($name);
		$this->prefix = $prefix;
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function get($key)
	{
		$valueObject = $this->ironcache->get($key);
		if($valueObject) {
			$uns = @unserialize($valueObject->value);
			if ($valueObject === 'b:0;' || $valueObject !== false) return $uns;
			else return $valueObject->value;
		} else {
			return null;
		}
	}

	/**
	 * Store an item in the cache for a given number of minutes.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  int     $minutes
	 * @return void
	 */
	public function put($key, $value, $minutes)
	{
		if(!is_scalar($value) && !is_array($value)) {
			$value = serialize($value);
		}

		// Ironcache's SDK allows you to do this by making the value an array with extra options.
		// More options can be found here: http://dev.iron.io/cache/reference/api/
		// Note: expiration can be no more then 30 days (2592000 seconds), but I don't check for this limit yet. I probably should.
		$valueArray = array(
			'value'         =>  $value,
			'expires_in'    =>  $minutes * 60,
		);

		$this->ironcache->put($key, $valueArray);
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $amount
	 * @return void
	 */
	public function increment($key, $amount = 1)
	{
		$this->ironcache->increment($key, $amount);
	}

	/**
	 * Decrement the amount of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $amount
	 * @return void
	 */
	public function decrement($key, $amount = 1)
	{
		$this->ironcache->increment($key, -$amount);
	}

	/**
	 * Store an item in the cache indefinitely.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function forever($key, $value)
	{
		if(!is_scalar($value) && !is_array($value)) {
			$value = serialize($value);
		}

		// Not specifying an expiration time will keep it forever
		$this->ironcache->put($key, $value);
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function forget($key)
	{
		$this->ironcache->delete($key);
	}

	/**
	 * Remove all items from the cache.
	 *
	 * @return void
	 */
	public function flush()
	{
		$this->ironcache->clear();
	}

	/**
	 * Get the cache key prefix.
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

}