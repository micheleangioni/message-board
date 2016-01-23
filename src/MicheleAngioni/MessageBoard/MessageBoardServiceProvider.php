<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\ServiceProvider;

class MessageBoardServiceProvider extends ServiceProvider {

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
        // Publish config files
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('ma_messageboard.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php', 'ma_messageboard'
        );

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../migrations/' => base_path('/database/migrations/messageboard'),
        ]);

        // Publish seeds
        $this->publishes([
            __DIR__.'/../../seeds/' => base_path('/database/seeds'),
        ]);

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../../seeds/', 'ma_messageboard');
	}

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerRepositories();

        // Register the HTML Purifier
        $this->app->register('Chromabits\Purifier\PurifierServiceProvider');
    }

    /**
     * Register the repositories that will handle all the database interaction.
     */
    protected function registerRepositories()
    {
        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\CommentRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\PermissionRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentPermissionRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\PostRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentPostRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\RoleRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\ViewRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentViewRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\PurifierInterface',
            'MicheleAngioni\MessageBoard\Purifier'
        );
    }

}
