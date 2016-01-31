<?php namespace MicheleAngioni\MessageBoard;

use Helpers;
use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Contracts\PermissionRepositoryInterface as PermissionRepo;
use MicheleAngioni\MessageBoard\Contracts\RoleRepositoryInterface as RoleRepo;
use MicheleAngioni\MessageBoard\Models\Permission;
use MicheleAngioni\MessageBoard\Models\Role;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PermissionManager
{
    protected $permissionRepo;

    protected $roleRepo;

    function __construct(PermissionRepo $permissionRepo, RoleRepo $roleRepo)
    {
        $this->permissionRepo = $permissionRepo;

        $this->roleRepo = $roleRepo;
    }

    /**
     * Return input Role.
     *
     * @param  int  $idRole
     * @throws ModelNotFoundException
     *
     * @return Role
     */
    public function getRole($idRole)
    {
        return $this->roleRepo->findOrFail($idRole);
    }

    /**
     * Return a collection of all available Roles.
     *
     * @return Collection
     */
    public function getRoles()
    {
        return $this->roleRepo->all();
    }

    /**
     * Return input Permission.
     *
     * @param  int  $idPermission
     * @throws ModelNotFoundException
     *
     * @return Permission
     */
    public function getPermission($idPermission)
    {
        return $this->permissionRepo->findOrFail($idPermission);
    }

    /**
     * Return a collection of all available Permissions.
     *
     * @return Collection
     */
    public function getPermissions()
    {
        return $this->permissionRepo->all();
    }

    /**
     * Create and return a new Role.
     * A Permission Collection or ids array can be passed as second parameter to be attached.
     *
     * @param  string  $name
     * @param  Collection|array  $permissions
     *
     * @return Role
     */
    public function createRole($name, $permissions = [])
    {
        $role = $this->roleRepo->create(['name' => $name]);

        if(is_array($permissions) && count($permissions) > 0) {
            $role->permissions()->attach($permissions);
        }
        elseif($permissions instanceof Collection) {
            $role->permissions()->attach($permissions->modelKeys());
        }

        return $role;
    }

    /**
     * Create a new Role. A Permission Collection or ids' array can be passed as second parameter to be attached.
     * Return true on success.
     *
     * @param  string  $name
     * @return Permission
     */
    public function createPermission($name)
    {
        return $this->permissionRepo->create(['name' => $name]);
    }

    /**
     * Attach input Permission to input Role.
     *
     * @param  Role|int  $role
     * @param  Permission|int  $permission
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     *
     * @return bool
     */
    public function attachPermission($role, $permission)
    {
        if(Helpers::isInt($role, 1)) {
            $role = $this->getRole($role);
        }
        elseif(!($role instanceof Role)) {
            throw new \InvalidArgumentException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.': $role is not a valid integer not a Role model.');
        }

        if(Helpers::isInt($permission, 1)) {
            $permission = $this->getRole($permission);
        }
        elseif(!($permission instanceof Permission)) {
            throw new \InvalidArgumentException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.': $permission is not a valid integer not a Permission model.');
        }

        $role->permissions()->sync([$permission->getKey()], false);

        return true;
    }

    /**
     * Detach input Permission from input Role.
     *
     * @param  Role|int  $role
     * @param  Permission|int  $permission
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     *
     * @return bool
     */
    public function detachPermission($role, $permission)
    {
        if(Helpers::isInt($role, 1)) {
            $role = $this->getRole($role);
        }
        elseif(!($role instanceof Role)) {
            throw new \InvalidArgumentException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.': $role is not a valid integer not a Role model.');
        }

        if(Helpers::isInt($permission, 1)) {
            $permission = $this->getRole($permission);
        }
        elseif(!($permission instanceof Permission)) {
            throw new \InvalidArgumentException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.': $permission is not a valid integer not a Permission model.');
        }

        $role->permissions()->detach($permission->getKey());

        return true;
    }

}
