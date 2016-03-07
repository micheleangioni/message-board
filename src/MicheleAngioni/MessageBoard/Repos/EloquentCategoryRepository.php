<?php

namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\CategoryRepositoryInterface;
use MicheleAngioni\MessageBoard\Models\Category;

class EloquentCategoryRepository extends AbstractEloquentRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }
}
