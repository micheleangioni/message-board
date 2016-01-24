<?php namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface;
use MicheleAngioni\MessageBoard\Models\Like;

class EloquentLikeRepository extends AbstractEloquentRepository implements LikeRepositoryInterface
{
    protected $model;


    public function __construct(Like $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityLike($entity, $userId)
    {
        if(is_string($entity)) {
            $type = $entity;
        }
        else {
            $type = get_class($entity);
        }

        return $entity->likes()
            ->where(['user_id' => $userId, 'likable_type' => $type])->first();
    }

}
