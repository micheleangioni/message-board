<?php namespace MicheleAngioni\MessageBoard\Models;

class Permission extends \Illuminate\Database\Eloquent\Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tb_messboard_permissions';


    public function roles()
    {
        return $this->belongsToMany('\MicheleAngioni\MessageBoard\Models\Role', 'tb_messboard_permission_role')->withTimestamps();
    }

}
