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
        $this->adminModel = Role::create(array(
            'id' => 1,
            'name' => 'Admin',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));

        $this->moderatorModel = Role::create(array(
            'id' => 2,
            'name' => 'Moderator',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));
    }

    protected function addPermissions()
    {
        Permission::create(array(
            'id' => 1,
            'name' => 'Edit Posts',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));

        Permission::create(array(
            'id' => 2,
            'name' => 'Delete Posts',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));

        Permission::create(array(
            'id' => 3,
            'name' => 'Ban Users',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));

        Permission::create(array(
            'id' => 10,
            'name' => 'Add Moderators',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));

        Permission::create(array(
            'id' => 11,
            'name' => 'Remove Moderators',
            'created_at' => $this->datetime,
            'updated_at' => $this->datetime,
        ));
    }

    protected function assignPermissions()
    {
        $this->adminModel->permissions()->sync(array(1, 2, 3, 10, 11), false);

        $this->moderatorModel->permissions()->sync(array(1, 2, 3), false);
    }

}
