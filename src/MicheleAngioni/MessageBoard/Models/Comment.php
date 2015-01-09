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
        return $this->belongsTo('\TopGames\Models\Tough\User');
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

}
