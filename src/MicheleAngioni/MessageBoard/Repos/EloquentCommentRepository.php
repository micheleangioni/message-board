<?php namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Models\Comment;

class EloquentCommentRepository extends AbstractEloquentRepository implements CommentRepositoryInterface
{
    protected $model;


    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

}
