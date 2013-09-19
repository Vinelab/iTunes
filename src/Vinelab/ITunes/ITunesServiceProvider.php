<?php namespace Vinelab\ITunes;

use Illuminate\Support\ServiceProvider;

class ITunesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('vinelab/itunes');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->app->register('Vinelab\Http\HttpServiceProvider');
		$this->app['vinelab.itunes'] = $this->app->share(function(){
			return new Agent($this->app['config'], $this->app['cache'], $this->app['vinelab.httpclient']);
		});

		$this->app->booting(function() {

			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('ITunes', 'Vinelab\ITunes\Facades\Agent');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}