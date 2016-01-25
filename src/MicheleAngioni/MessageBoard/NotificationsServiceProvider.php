<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider {

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

	}

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerRepositories();
    }

    /**
     * Register the repositories that will handle all the database interaction.
     */
    protected function registerRepositories()
    {
        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\NotificationRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentNotificationRepository'
        );
    }

}
