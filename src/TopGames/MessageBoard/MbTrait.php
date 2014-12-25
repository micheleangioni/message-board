<?php

namespace TopGames\MessageBoard;

trait MbTrait {


    public function mbPosts()
    {
        return $this->hasMany('\TopGames\MessageBoard\Models\Post');
    }

    public function mbLastView()
    {
        return $this->hasOne('\TopGames\MessageBoard\Models\View');
    }

    /**
     * Return the User last view datetime.
     *
     * @return string
     */
    public function getLastViewDatetime()
    {
        return $this->mbLastView->datetime;
    }

}