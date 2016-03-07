<?php

namespace MicheleAngioni\MessageBoard\Models;

class Ban extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_bans';

    protected $guarded = array('id', 'user_id');


    public function user()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }

    // Getters

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getUntil()
    {
        return $this->until;
    }
}
