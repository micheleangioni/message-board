<?php

use MicheleAngioni\MessageBoard\Models\Permission;
use MicheleAngioni\MessageBoard\Models\Role;

class MessageBoardSeeder extends Illuminate\Database\Seeder {

    /**
     * @var Role
     */
    protected $adminModel;

    /**
     * @var string
     */
    protected $datetime;

    /**
     * @var Role
     */
    protected $moderatorModel;

    /**
     * Seed the roles and permissions tables.
     */
    public function run()
	{
        Eloquent::unguard();

        $this->datetime = date('Y-m-d H:i:s');

        $this->addRoles();

        $this->addPermissions();

        $this->assignPermissions();
	}


    protected function addRoles()
    {
        $this->adminModel = Role::create([
            'id' => 1,
            'name' => 'Admin',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        $this->moderatorModel = Role::create([
            'id' => 2,
            'name' => 'Moderator',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);
    }

    protected function addPermissions()
    {
        Permission::create([
            'id' => 1,
            'name' => 'Edit Posts',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        Permission::create([
            'id' => 2,
            'name' => 'Delete Posts',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        Permission::create([
            'id' => 3,
            'name' => 'Ban Users',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        Permission::create([
            'id' => 11,
            'name' => 'Add Moderators',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        Permission::create([
            'id' => 12,
            'name' => 'Remove Moderators',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);

        Permission::create([
            'id' => 21,
            'name' => 'Manage Categories',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ]);
    }

    protected function assignPermissions()
    {
        $this->adminModel->permissions()->sync([1, 2, 3, 11, 12, 21], false);

        $this->moderatorModel->permissions()->sync([1, 2, 3], false);
    }

}
