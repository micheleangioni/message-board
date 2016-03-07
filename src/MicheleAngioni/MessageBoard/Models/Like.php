<?php

namespace MicheleAngioni\MessageBoard\Models;

class Like extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_likes';

    protected $guarded = array('id');


    public function likable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }


    // Getters

    public function getAuthor()
    {
        return $this->user;
    }

    public function getAuthorId()
    {
        return $this->user_id;
    }

    public function getOwner()
    {
        return $this->likable->getOwner();
    }

    public function getOwnerId()
    {
        return $this->likable->getOwnerId();
    }

    public function getLikable()
    {
        return $this->likable;
    }

    public function getLikableId()
    {
        return $this->likable_id;
    }

    public function getLikableType()
    {
        return $this->likable_type;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }


    // Others

    /**
     * Check if the Post author wrote on his own message board.
     *
     * @return bool
     */
    public function authorLikesOwnEntity()
    {
        if($this->getAuthorId() == $this->getOwnerId()) {
            return true;
        }
        else {
            return false;
        }
    }
}
