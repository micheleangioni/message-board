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

}