<?php namespace MicheleAngioni\MessageBoard\Contracts;

interface LikeEventInterface {

    /**
     * Return the Like model
     *
     * @return \MicheleAngioni\MessageBoard\Models\Like
     */
    public function getLike();

}
