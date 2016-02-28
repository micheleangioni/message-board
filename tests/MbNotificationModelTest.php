<?php

class MbNotificationModelTest extends Orchestra\Testbench\TestCase {

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
            'Mews\Purifier\PurifierServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\MessageBoardServiceProvider'
        );
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ma_messageboard.model', 'User');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

	public function testGetCleanText()
	{
        date_default_timezone_set('UTC');
        $datetime = date('Y-m-d H:i:s');

        $notification = new MicheleAngioni\MessageBoard\Models\Notification;
        $notification->to_id = 1;
        $notification->type = 'type';
        $notification->text = 'dirty <? text';
        $notification->read = false;
        $notification->created_at = $datetime;

        $this->assertNotEquals($notification->getText(), $notification->getCleanText());
        $this->assertNotRegExp('/\<\?/', $notification->getCleanText());
    }

}
