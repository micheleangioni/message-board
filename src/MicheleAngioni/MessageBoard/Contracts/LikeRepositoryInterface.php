<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface LikeRepositoryInterface
{
    /**
     * Return the Like of input entity (class name or object) and user.
     * Return null if no Like is found.
     *
     * @param  object|string  $entity
     * @param  int  $userId
     *
     * @return \MicheleAngioni\MessageBoard\Models\Like|null
     */
    public function getUserEntityLike($entity, $userId);

}
