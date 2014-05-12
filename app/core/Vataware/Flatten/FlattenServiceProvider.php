<?php
namespace Vataware\Flatten;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\FileLoader as ConfigLoader;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Flatten\FlattenServiceProvider as ServiceProvider;
use Philf\Setting\Setting;

/**
 * Register the Flatten package with the Laravel framework
 */
class FlattenServiceProvider extends ServiceProvider
{
	/**
	 * Boot Flatten
	 */
	public function boot()
	{
		// Register templating methods
		$this->app['flatten.templating']->registerTags();

		// Cancel if Flatten shouldn't run here
		if (!$this->app['flatten.context']->shouldRun()) {
			return false;
		}

		// Launch startup event
		$this->app['flatten']->start();
	}
}
