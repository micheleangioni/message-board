<?php

namespace TopGames\MessageBoard\Repos;

use TopGames\Support\Repos\AbstractEloquentRepository;
use TopGames\MessageBoard\Models\Comment;

class EloquentCommentRepository extends AbstractEloquentRepository implements CommentRepositoryInterface
{
    protected $model;


    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

}