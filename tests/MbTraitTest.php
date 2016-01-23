<?php

class MbTraitTest extends Orchestra\Testbench\TestCase {

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Create an artisan object for calling migrations
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        // Call migrations
        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--path' => 'migrations',
        ]);

        // Call migrations specific to our tests
        $artisan->call('migrate', array(
            '--database' => 'testbench',
            '--path' => '../tests/migrations',
        ));

        // Call seeding
        Artisan::call('db:seed', ['--class' => 'MessageBoardSeeder']);
    }

    /**
     * Get base path.
     *
     * @return string
     */
    protected function getBasePath()
    {
        // reset base path to point to our package's src directory
        return __DIR__.'/../src';
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


	public function testTrait()
	{
        $roles = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository')->all();

        $user = new UserMb;
        $user->id = 1;
        $user->save();

        // Test attach and detach roles
        $user->attachMbRole($roles[0]);
        $this->assertEquals(1, count($user->mbRoles));
        $this->assertTrue($user->canMb($roles[0]->permissions[0]->name));

        $user->detachMbRole($roles[0]);
        $user = UserMb::find(1);
        $this->assertEquals(0, count($user->mbRoles));

        // Test isBanned
        $user->mbBans()->create(array(
            'user_id' => 1,
            'reason' => 'Reason',
            'until' => '2100-01-01'
        ));

        $this->assertTrue($user->isBanned());
    }



    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }

    public function tearDown()
    {
        Mockery::close();
    }

}

/**
 * A stub class that implements MbUserInterface and uses the MbTrait trait.
 */
class UserMb extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
{
    use MicheleAngioni\MessageBoard\MbTrait;

    protected $table = 'users';

    public function getPrimaryId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
