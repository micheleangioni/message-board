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
		$this->package('michele-angioni/message-board');
	}

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerRepositories();

        // Register the HTML Purifier

        $this->app->register('Mews\Purifier\PurifierServiceProvider');
    }

    /**
     * Register the repositories that will handle all the database interaction.
     */
    protected function registerRepositories()
    {
        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\CommentRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\LikeRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\PermissionRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentPermissionRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\PostRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentPostRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\RoleRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\Repos\ViewRepositoryInterface',
            'MicheleAngioni\MessageBoard\Repos\EloquentViewRepository'
        );

        $this->app->bind(
            'MicheleAngioni\MessageBoard\PurifierInterface',
            'MicheleAngioni\MessageBoard\Purifier'
        );
    }

}
