<?php

namespace TopGames\MessageBoard\Repos;

use TopGames\Support\Repos\AbstractEloquentRepository;
use TopGames\MessageBoard\Models\Like;

class EloquentLikeRepository extends AbstractEloquentRepository implements LikeRepositoryInterface
{
    protected $model;


    public function __construct(Like $model)
    {
        $this->model = $model;
    }

    /**
     * Return the Like of input entity and user. Return null if no Like is found.
     *
     * @param  object  $entity
     * @param  int     $userId
     *
     * @return Like|bool
     */
    public function getUserEntityLike($entity, $userId)
    {
        return $entity->likes()
            ->where(['user_id' => $userId, 'likable_type' => get_class($entity)])->first();
    }

}