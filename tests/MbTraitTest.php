<?php

class MbTraitTest extends Orchestra\Testbench\TestCase
{
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
            'MicheleAngioni\MessageBoard\Providers\MessageBoardServiceProvider'
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

        $this->assertFalse($user->isBanned());
        $this->assertNull($user->getBan());

        // Test attach and detach roles
        $user->attachMbRole($roles[0]);
        $this->assertEquals(1, count($user->mbRoles));
        $this->assertTrue($user->canMb($roles[0]->permissions[0]->name));

        $user->detachMbRole($roles[0]);
        $user = UserMb::find(1);
        $this->assertEquals(0, count($user->mbRoles));

        // Ban the User
        $user->mbBans()->create([
            'user_id' => 1,
            'reason' => 'Reason',
            'until' => '2100-01-01'
        ]);

        // Reload Model
        $user = $user->fresh();

        // Test isBanned
        $this->assertTrue($user->isBanned());

        // Test getBan
        $ban = $user->getBan();
        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Ban', $ban);
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
