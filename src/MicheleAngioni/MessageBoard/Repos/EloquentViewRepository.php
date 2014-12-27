<?php

namespace MicheleAngioni\MessageBoard\Repos;

use TopGames\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Models\View;

class EloquentViewRepository extends AbstractEloquentRepository implements ViewRepositoryInterface
{
    protected $model;


    public function __construct(View $model)
    {
        $this->model = $model;
    }

}