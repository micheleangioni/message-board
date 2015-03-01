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
        return $this->belongsTo(\Config::get('auth.model'));
    }

    public function user()
    {
        return $this->belongsTo(\Config::get('auth.model'));
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

    /**
     * Set the custom_datetime attribute, i.e. the most recent datetime of the post AND its comments.
     * If the attribute is already defined, return it. Otherwise compute and then return it.
     */
    public function setChildDatetime()
    {
        $this->childDatetime = (string)$this->created_at;

        if(!$this->comments->isEmpty())
        {
            $comments = $this->comments->sortBy('created_at');

            if($this->childDatetime < $lastCommentDatetime = (string)$comments->last()->created_at)
            {
                $this->childDatetime = $lastCommentDatetime;
            }
        }
    }

}
