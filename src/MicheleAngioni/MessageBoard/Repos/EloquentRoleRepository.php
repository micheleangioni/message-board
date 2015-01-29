<?php namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Models\Role;

class EloquentRoleRepository extends AbstractEloquentRepository implements RoleRepositoryInterface
{
    protected $model;


    public function __construct(Role $model)
    {
        $this->model = $model;
    }

}
