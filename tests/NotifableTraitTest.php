<?php

class NotifableTraitTest extends Orchestra\Testbench\TestCase {

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
            'MicheleAngioni\MessageBoard\MessageBoardServiceProvider',
            'MicheleAngioni\MessageBoard\NotificationsServiceProvider'
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


	public function testTraitRelationships()
	{
        $user = new UserNotifable;
        $user->id = 1;
        $user->save();

        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Relations\HasMany', $user->notifications());
    }

    public function testTraitMethods()
    {
        $idUser = 1;

        $user = new UserNotifable;
        $user->id = $idUser;
        $user->save();

        // Test Notifications relation
        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');

        // Test Notification methods
        $this->assertNull($user->getLastNotification());

        $notificationService->sendNotification($idUser, 'from_type', 2, null, 'Notification text', null, null, []);
        $unreadNotifications = $user->getNotificationsNotRead();
        $this->assertEquals(1, $unreadNotifications->count());
        $this->assertEquals(1, $user->countNotificationsNotRead());
        $this->assertNotNull($user->getLastNotification());

        $user->readAllNotifications();
        $unreadNotifications = $user->getNotificationsNotRead();
        $this->assertEquals(0, $unreadNotifications->count());
        $this->assertEquals(0, $user->countNotificationsNotRead());
        $this->assertNotNull($user->getLastNotification());

        $user->deleteAllNotifications();
        $this->assertNull($user->getLastNotification());
        $this->assertEquals(0, $user->countNotificationsNotRead());
        $this->assertEquals(0, $user->getNotifications()->count());
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
class UserNotifable extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
{
    use MicheleAngioni\MessageBoard\MbTrait;
    use MicheleAngioni\MessageBoard\Notifable;

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
