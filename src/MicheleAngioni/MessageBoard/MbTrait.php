<?php namespace MicheleAngioni\MessageBoard;

use MicheleAngioni\MessageBoard\Models\Role;

trait MbTrait {


    public function mbPosts()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Post');
    }

    public function mbLastView()
    {
        return $this->hasOne('\MicheleAngioni\MessageBoard\Models\View');
    }

    public function mbRoles()
    {
        return $this->belongsToMany('\MicheleAngioni\MessageBoard\Models\Role', 'tb_messboard_user_role', 'user_id')->withTimestamps();
    }

    /**
     * Return the User last view datetime.
     *
     * @return string
     */
    public function getLastViewDatetime()
    {
        return $this->mbLastView->datetime;
    }

    /**
     * Checks if the user has a Role by its name.
     *
     * @param string $name
     * @return bool
     */
    public function hasMbRole($name)
    {
        foreach ($this->mbRoles as $role) {
            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a Permission by its name.
     *
     * @param string $permission
     * @return bool
     */
    public function canMb($permission)
    {
        foreach ($this->mbRoles as $role) {
            // Deprecated permission value within the role table.
            if (is_array($role->permissions) && in_array($permission, $role->permissions) ) {
                return true;
            }

            // Validate against the Permission table
            foreach ($role->permissions as $perm) {
                if ($perm->name == $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Attach input Role to the user.
     *
     * @param Role|array $role
     * @return void
     */
    public function attachMbRole($role)
    {
        if( is_object($role)) {
            $role = $role->getKey();
        }

        if( is_array($role)) {
            $role = $role['id'];
        }

        $this->mbRoles()->attach( $role );
    }

    /**
     * Detach input Role from the user.
     *
     * @param Role $role
     * @return void
     */
    public function detachMbRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->mbRoles()->detach($role);
    }
}
