<?php namespace MicheleAngioni\MessageBoard\Models;

class Role extends \Illuminate\Database\Eloquent\Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tb_messboard_roles';


    public function permissions()
    {
        return $this->belongsToMany('\MicheleAngioni\MessageBoard\Models\Permission', 'tb_messboard_permission_role');
    }

    public function user()
    {
        return $this->belongsToMany(\Config::get('auth.model'), 'tb_messboard_user_role', 'role_id');
    }

}
