<?php namespace MicheleAngioni\MessageBoard;

trait MbTrait {


    public function mbPosts()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Post');
    }

    public function mbLastView()
    {
        return $this->hasOne('\MicheleAngioni\MessageBoard\Models\View');
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
