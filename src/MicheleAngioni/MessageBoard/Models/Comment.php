<?php namespace MicheleAngioni\MessageBoard\Models;

class Comment extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_comments';

    protected $guarded = array('id');


    public function likes()
    {
        return $this->morphMany('\MicheleAngioni\MessageBoard\Models\Like', 'likable');
    }

    public function post()
    {
        return $this->belongsTo('\MicheleAngioni\MessageBoard\Models\Post');
    }

    public function user()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }


    /**
     * Override the standard delete, deleting all related likes.
     */
    public function delete()
    {
        // Delete related likes
        $this->likes()->delete();

        parent::delete();
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
        return $this->post->getOwner();
    }

    public function getOwnerId()
    {
        return $this->post->getOwnerId();
    }

    public function getText()
    {
        return $this->text;
    }

}
