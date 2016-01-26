<?php namespace MicheleAngioni\MessageBoard\Models;

class Post extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_posts';

    protected $guarded = array('id');

    // Variables in $append array will be included in both the model's array and JSON forms
    protected $appends = array('child_datetime');

    /**
     * @var string
     */
    protected $childDatetime;


    public function comments()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Comment');
    }

    public function likes()
    {
        return $this->morphMany('\MicheleAngioni\MessageBoard\Models\Like', 'likable');
    }

    public function poster()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }

    public function user()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }


    // Custom Attributes

    /**
     * Return the child datetime attribute.
     *
     * @return string
     */
    public function getChildDatetimeAttribute()
    {
        $this->setChildDatetime();

        return $this->childDatetime;
    }

    /**
     * Override the standard delete, deleting all related likes and comments.
     */
    public function delete()
    {
        // Delete related comments
        foreach($this->comments as $comment) {
            $comment->delete();
        }

        // Delete related likes
        $this->likes()->delete();

        parent::delete();
    }


    // Getters

    public function getAuthor()
    {
        return $this->poster;
    }

    public function getAuthorId()
    {
        return $this->poster_id;
    }

    public function getOwner()
    {
        return $this->user;
    }

    public function getOwnerId()
    {
        return $this->user_id;
    }

    public function getText()
    {
        return $this->text;
    }


    // Other Methods

    /**
     * Return false if the Post has been read, false otherwise.
     *
     * @return bool
     */
    public function isRead()
    {
        if($this->is_read) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Set the custom_datetime attribute, i.e. the most recent datetime of the post AND its comments.
     * If the attribute is already defined, return it. Otherwise compute and then return it.
     */
    public function setChildDatetime()
    {
        $this->childDatetime = (string)$this->created_at;

        if(!$this->comments->isEmpty()) {
            $comments = $this->comments->sortBy('created_at');

            if($this->childDatetime < $lastCommentDatetime = (string)$comments->last()->created_at) {
                $this->childDatetime = $lastCommentDatetime;
            }
        }
    }

    /**
     * Check if the Post author wrote on his own message board.
     *
     * @return bool
     */
    public function isInAuthorMb()
    {
        if($this->getAuthorId() == $this->getOwnerId()) {
            return true;
        }
        else {
            return false;
        }
    }

}
