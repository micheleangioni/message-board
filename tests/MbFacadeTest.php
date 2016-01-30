<?php

class MbFacadeTest extends Orchestra\Testbench\TestCase {

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
        $app['config']->set('ma_messageboard.notifications.after_mb_events', true);

        $app['config']['ma_messageboard.model'] = 'UserFacade';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';
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
            'Helpers' => 'MicheleAngioni\Support\Facades\Helpers',
            'MessageBoard' => 'MicheleAngioni\MessageBoard\Facades\MessageBoard',
        );
    }

	public function testPostCreation()
	{
        $user = new UserFacade();
        $user->id = 1;

        $post = \MessageBoard::createPost($user, null, null, 'text', false);

        $this->assertInstanceOf('\MicheleAngioni\MessageBoard\Models\Post', $post);

        $postRetrieved = \MessageBoard::getPost($post->getKey());
        $this->assertEquals($postRetrieved->getKey(), $post->getKey());
    }

}


/**
 * A stub class that implements MbUserInterface and uses the MbTrait trait.
 */
class UserFacade extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
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
