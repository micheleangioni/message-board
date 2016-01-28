<?php

class MbNotificationServiceTest extends Orchestra\Testbench\TestCase {

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


	public function testNotificationCreateAndGet()
	{
        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');

        $this->assertEquals(0, $notificationService->getNotifications()->count());

        $notification = $notificationService->sendNotification(1, 'from_type', 2, null, 'Notification text', null, null, []);

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Notification', $notification);

        $this->assertEquals(1, $notificationService->getNotifications()->count());

        $notification = $notificationService->getNotification($notification->getKey());

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Notification', $notification);
    }

    public function testReadNotification()
    {
        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');

        $notification = $notificationService->sendNotification(1, 'from_type', 2, null, 'Notification text', null, null, []);
        $this->assertFalse($notification->isRead());

        $notificationService->readNotification($notification->getKey());

        $notification = $notificationService->getNotification($notification->getKey());

        $this->assertTrue($notification->isRead());
    }

    public function testReadAllNotifications()
    {
        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');

        $toId = 1;

        $notification = $notificationService->sendNotification($toId, 'from_type', 2, null, 'Notification text', null, null, []);
        $this->assertFalse($notification->isRead());

        $notificationService->readAllNotifications($toId);

        $notification = $notificationService->getNotification($notification->getKey());

        $this->assertTrue($notification->isRead());
    }

    public function testDeleteOldNotifications()
    {
        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');

        $notification = $notificationService->sendNotification(1, 'from_type', 2, null, 'Notification text', null, null, []);
        $notification->created_at = \Helpers::getDate(-10, $format = 'Y-m-d H:i:s');
        $notification->save();

        $notificationService->deleteOldNotifications(\Helpers::getDate(-5, $format = 'Y-m-d H:i:s'));

        $this->assertEquals(0, $notificationService->getNotifications()->count());
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
