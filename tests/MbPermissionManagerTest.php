<?php

class MbPermissionManagerTest extends Orchestra\Testbench\TestCase {

    protected $permissionRepo;

    protected $roleRepo;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Call migrations
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/migrations'),
        ]);

        // Call migrations specific to our tests
        $this->artisan('migrate', array(
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/migrations'),
        ));

        // Call seeding
        $this->artisan('db:seed', ['--class' => 'MessageBoardSeeder']);
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

    /**
     * Get package providers. At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array(
            'MicheleAngioni\Support\SupportServiceProvider',
            'MicheleAngioni\MessageBoard\MessageBoardServiceProvider'
        );
    }

    /**
     * Get package aliases. In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file. If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return array(
            'Config' => 'Illuminate\Support\Facades\Config',
            'Helpers' => 'MicheleAngioni\Support\Facades\Helpers',
        );
    }


	public function testRolesAndPermissionGetters()
	{
        $permissionManager = $this->app->make('MicheleAngioni\MessageBoard\PermissionManager');

        $role = $permissionManager->getRole(1);
        $this->assertNotNull($role);

        $roles = $permissionManager->getRoles();
        $this->assertGreaterThan(1, count($roles));

        $permission = $permissionManager->getPermission(1);
        $this->assertNotNull($permission);

        $permissions = $permissionManager->getPermissions();
        $this->assertGreaterThan(1, count($permissions));
    }

    public function testCreateRole()
    {
        $permissionManager = $this->app->make('MicheleAngioni\MessageBoard\PermissionManager');

        $permissions = $permissionManager->getPermissions();
        $rolesNumberBefore = count($permissionManager->getRoles());

        $newRole = $permissionManager->createRole('New Role', $permissions);

        $rolesNumberAfter = count($permissionManager->getRoles());
        $this->assertEquals($rolesNumberBefore + 1, $rolesNumberAfter);

        $this->assertGreaterThan(0, count($newRole->permissions));
    }

    public function testCreatePermission()
    {
        $permissionManager = $this->app->make('MicheleAngioni\MessageBoard\PermissionManager');

        $permissionsNumberBefore = count($permissionManager->getPermissions());

        $permissionManager->createPermission('New Permission');

        $permissionsNumberAfter = count($permissionManager->getPermissions());
        $this->assertEquals($permissionsNumberBefore + 1, $permissionsNumberAfter);
    }

}
