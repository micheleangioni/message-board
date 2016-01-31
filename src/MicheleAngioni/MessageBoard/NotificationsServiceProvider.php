<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\ServiceProvider;
use Event;

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
		// Register the Message Board events handler

        Event::subscribe('MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerRepositories();

        $this->registerFacade();
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

    /**
     * Register the Notification facade
     */
    protected function registerFacade()
    {
        $this->app->singleton('mbnotifications', function ($app) {
            $notificationRepository = $app->make('MicheleAngioni\MessageBoard\Contracts\NotificationRepositoryInterface');

            return new \MicheleAngioni\MessageBoard\Services\NotificationService($notificationRepository);
        });
    }

}
