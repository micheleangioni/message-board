<?php namespace MicheleAngioni\MessageBoard\Models;

class View extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_views';

    protected $guarded = array('id');


    public function user()
    {
        return $this->belongsTo('\TopGames\Models\Tough\User');
    }

}