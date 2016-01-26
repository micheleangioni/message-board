<?php namespace MicheleAngioni\MessageBoard\Contracts;

interface PostEventInterface {

    /**
     * Return the Post model
     *
     * @return \MicheleAngioni\MessageBoard\Models\Post
     */
    public function getPost();

}
