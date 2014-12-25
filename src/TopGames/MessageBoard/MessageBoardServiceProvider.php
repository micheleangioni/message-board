<?php namespace TopGames\MessageBoard;

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
		$this->package('top-games/message-board');
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
            'TopGames\MessageBoard\Repos\CommentRepositoryInterface',
            'TopGames\MessageBoard\Repos\EloquentCommentRepository'
        );

        $this->app->bind(
            'TopGames\MessageBoard\Repos\LikeRepositoryInterface',
            'TopGames\MessageBoard\Repos\EloquentLikeRepository'
        );

        $this->app->bind(
            'TopGames\MessageBoard\Repos\PostRepositoryInterface',
            'TopGames\MessageBoard\Repos\EloquentPostRepository'
        );

        $this->app->bind(
            'TopGames\MessageBoard\Repos\ViewRepositoryInterface',
            'TopGames\MessageBoard\Repos\EloquentViewRepository'
        );
    }

}
