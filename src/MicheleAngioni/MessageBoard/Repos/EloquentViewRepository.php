<?php

namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\ViewRepositoryInterface;
use MicheleAngioni\MessageBoard\Models\View;

class EloquentViewRepository extends AbstractEloquentRepository implements ViewRepositoryInterface
{
    protected $model;

    public function __construct(View $model)
    {
        $this->model = $model;
    }
}
