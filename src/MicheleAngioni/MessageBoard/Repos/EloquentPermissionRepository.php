<?php

namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\PermissionRepositoryInterface;
use MicheleAngioni\MessageBoard\Models\Permission;

class EloquentPermissionRepository extends AbstractEloquentRepository implements PermissionRepositoryInterface
{
    protected $model;

    public function __construct(Permission $model)
    {
        $this->model = $model;
    }
}
