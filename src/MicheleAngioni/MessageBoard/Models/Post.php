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
    protected $child_datetime;


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
        return $this->belongsTo('\TopGames\Models\Tough\User');
    }

    public function user()
    {
        return $this->belongsTo('\TopGames\Models\Tough\User');
    }


    // Custom Attributes

    /**
     * Define the custom_datetime attribute, i.e. the most recent datetime of the post AND its comments.
     * If the attribute is already defined, return it. Otherwise compute and then return it.
     *
     * @return string
     */
    public function getChildDatetimeAttribute()
    {
        if(!$this->child_datetime)
        {
            $this->child_datetime = (string)$this->created_at;

            if(!$this->comments->isEmpty())
            {
                $this->comments = $this->comments->sortBy('created_at')->reverse();

                if($this->child_datetime < $lastCommentDatetime = (string)$this->comments->first()->created_at)
                {
                    $this->child_datetime = $lastCommentDatetime;
                }
            }
        }

        return $this->child_datetime;
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

}
