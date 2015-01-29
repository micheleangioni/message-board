<?php

class MbDbSeedingTest extends Orchestra\Testbench\TestCase {

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Create an artisan object for calling migrations
        $artisan = $this->app->make('artisan');

        // Call migrations
        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--path' => 'migrations',
        ]);

        // Call migrations specific to our tests
        $artisan->call('migrate', array(
            '--database' => 'testbench',
            '--path' => '/migrations',
        ));

        // Call seeding
        Artisan::call('db:seed');
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // reset base path to point to our package's src directory
        $app['path.base'] = __DIR__ . '/../src';
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
    protected function getPackageProviders()
    {
        return array(
            'Illuminate\Database\DatabaseServiceProvider',
            'Illuminate\Database\SeedServiceProvider',
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
    protected function getPackageAliases()
    {
        return array(
            'Config' => 'Illuminate\Support\Facades\Config',
            'DB' => 'Illuminate\Support\Facades\DB',
            'Helpers' => 'MicheleAngioni\Support\Facades\Helpers',
            'Seeder' => 'Illuminate\Database\Seeder',
        );
    }

	public function testSeeding()
	{
        $roles = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository')->all();
        $permissions = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPermissionRepository')->count();

        $this->assertGreaterThan(1, count($roles));
        $this->assertGreaterThan(1, $permissions);
        $this->assertGreaterThan(0, count($roles[0]->permissions));
        $this->assertGreaterThan(0, count($roles[1]->permissions));
    }

}
