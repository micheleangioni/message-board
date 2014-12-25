<?php

namespace TopGames\MessageBoard\Repos;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

    public function register()
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