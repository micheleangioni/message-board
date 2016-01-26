<?php namespace MicheleAngioni\MessageBoard\Contracts;

interface CommentEventInterface {

    /**
     * Return the Comment model
     *
     * @return \MicheleAngioni\MessageBoard\Models\Comment
     */
    public function getComment();

}
