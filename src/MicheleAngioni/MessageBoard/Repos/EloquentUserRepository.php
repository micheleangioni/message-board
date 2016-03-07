<?php

namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\UserRepositoryInterface;

class EloquentUserRepository extends AbstractEloquentRepository implements UserRepositoryInterface
{
    protected $model;

    protected $modelClassName;

    public function __construct()
    {
        $this->modelClassName = config('ma_messageboard.model');

        $this->model = new $this->modelClassName;
    }
}
