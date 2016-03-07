<?php

namespace MicheleAngioni\MessageBoard;

use Helpers;

trait MbTrait
{
    public function mbBans()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Ban', 'user_id')->orderBy('updated_at', 'desc');
    }

    public function mbLastView()
    {
        return $this->hasOne('\MicheleAngioni\MessageBoard\Models\View', 'user_id');
    }

    public function mbPosts()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Post', 'user_id');
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
     * Return the User Posts.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPosts()
    {
        return $this->mbPosts;
    }

    /**
     * Check if the user is actually banned.
     *
     * @return bool
     */
    public function isBanned()
    {
        if (count($this->mbBans) > 0) {
            if ($this->mbBans->last()->getUntil() >= Helpers::getDate()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the current Ban.
     * Return null if the User is not banned.
     *
     * @return \MicheleAngioni\MessageBoard\Models\Ban|null
     */
    public function getBan()
    {
        if(count($this->mbBans) > 0) {
            $ban = $this->mbBans->last();

            if ($ban->getUntil() >= Helpers::getDate()) {
                return $ban;
            }
        }

        return null;
    }

    /**
     * Checks if the user has a Role by its name.
     *
     * @param  string  $name
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
     * @param  string  $permission
     * @return bool
     */
    public function canMb($permission)
    {
        foreach ($this->mbRoles as $role) {
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
     * @param \MicheleAngioni\MessageBoard\Models\Role|array  $role
     * @return void
     */
    public function attachMbRole($role)
    {
        if (is_object($role)) {
            $idRole = $role->getKey();
        }

        if (is_array($role)) {
            $idRole = $role['id'];
        }

        $this->mbRoles()->sync([$idRole], false);
    }

    /**
     * Detach input Role from the user.
     *
     * @param \MicheleAngioni\MessageBoard\Models\Role|array  $role
     * @return void
     */
    public function detachMbRole($role)
    {
        if (is_object($role)) {
            $idRole = $role->getKey();
        }

        if (is_array($role)) {
            $idRole = $role['id'];
        }

        $this->mbRoles()->detach($idRole);
    }
}
