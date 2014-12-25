<?php

namespace TopGames\MessageBoard\Repos;

use TopGames\Support\Repos\AbstractEloquentRepository;
use TopGames\MessageBoard\Models\View;

class EloquentViewRepository extends AbstractEloquentRepository implements ViewRepositoryInterface
{
    protected $model;


    public function __construct(View $model)
    {
        $this->model = $model;
    }

}