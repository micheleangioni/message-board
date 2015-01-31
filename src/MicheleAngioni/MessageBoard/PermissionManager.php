<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\PurifierInterface;
use MicheleAngioni\MessageBoard\Models\Permission;
use MicheleAngioni\MessageBoard\Models\Role;
use MicheleAngioni\MessageBoard\Repos\PermissionRepositoryInterface as PermissionRepo;
use MicheleAngioni\MessageBoard\Repos\RoleRepositoryInterface as RoleRepo;
use InvalidArgumentException;

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
     * A Permission Collection or ids' array can be passed as second parameter to be attached.
     *
     * @param  string  $name
     * @param  Collection|array|null  $permissions
     *
     * @return Role
     */
    public function createRole($name, $permissions = NULL)
    {
        $role = $this->roleRepo->create(['name' => $name]);

        if(is_array($permissions)) {
            $role->permissions()->attach($permissions);
        }

        if($permissions instanceof Collection) {
            foreach($permissions as $perm) {
                $role->permissions()->save($perm);
            }
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

}
