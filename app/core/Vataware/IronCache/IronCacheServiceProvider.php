<?php namespace Vataware\IronCache;

use Dietervds\IronCache\IronCacheServiceProvider as BaseServiceProvider;
use Illuminate\Cache\Repository;

class IronCacheServiceProvider extends BaseServiceProvider {

	public function boot()
	{
		$this->package('dietervds/laravel4-ironcache');

		$this->app['cache']->extend('ironcache', function($app)
		{
			$prefix = $app['config']->get('cache.prefix');
			$config = $app['config']->get('cache.iron');

			return new Repository(new IronCacheStore($prefix, $config, $app));
		});
	}
}