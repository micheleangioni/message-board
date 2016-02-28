<?php

class MbEventsTest extends Orchestra\Testbench\TestCase {

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
            'MicheleAngioni\MessageBoard\Providers\MessageBoardServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\NotificationsServiceProvider'
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

	public function testNotificationCreatedAfterNewPostAndComment()
	{
        // Following path for base path is needed by Purifier package
        $this->app['path.base'] .= '/../../../..';
        $this->app['config']['ma_messageboard.model'] = 'UserEvent';
        $this->app['config']['ma_messageboard.posts_per_page'] = 20;
        $this->app['config']['ma_messageboard.user_named_route'] = 'user';

        $idSender = 1;
        $idReceiver = 2;
        $idSender2 = 3;

        // Create a new Post with a Category

        $senderPost = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $senderPost->shouldReceive('getPrimaryId')
            ->andReturn($idSender);

        $receiver = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $receiver->shouldReceive('getPrimaryId')
            ->andReturn($idReceiver);

        $category = new \MicheleAngioni\MessageBoard\Models\Category();
        $category->name = 'category';
        $category->default_pic = 'filename.jpg';
        $category->save();

        $mbGateway = $this->app->make('MicheleAngioni\MessageBoard\MbGateway');
        $post = $mbGateway->createPost($receiver,  $senderPost, $category->getKey(), 'text', false);

        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');
        $this->assertEquals(1, $notificationService->getNotifications()->count());
        $postNotification = $notificationService->getNotifications()->first();
        $this->assertEquals($idSender, $postNotification->from_id);
        $this->assertEquals($idReceiver, $postNotification->to_id);
        $this->assertEquals(0, $postNotification->read);
        $this->assertEquals(config('ma_messageboard.pic_path') . DIRECTORY_SEPARATOR . 'filename.jpg',
            $postNotification->pic_url);

        // Create a new Comment

        $senderComment = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $senderComment->shouldReceive('getPrimaryId')
            ->andReturn($idSender2);

        $mbGateway->createComment($senderComment, $post->getkey(), 'text', false);
        $this->assertEquals(2, $notificationService->getNotifications()->count());

        $commentNotification = $notificationService->getNotifications()->last();
        $this->assertEquals($idSender2, $commentNotification->from_id);
        $this->assertEquals($idReceiver, $commentNotification->to_id);
        $this->assertEquals(0, $commentNotification->read);
        $this->assertEquals('', $commentNotification->pic_url);
    }

    public function testNotificationCreatedAfterNewPostLike()
    {
        // Following path for base path is needed by Purifier package
        $this->app['path.base'] .= '/../../../..';
        $this->app['config']['ma_messageboard.model'] = 'UserEvent';
        $this->app['config']['ma_messageboard.posts_per_page'] = 20;
        $this->app['config']['ma_messageboard.user_named_route'] = 'user';

        $idUserLiker = 1;
        $idReceiver = 2;
        $idSender = 3;

        // Create a new Post with a Category

        $senderPost = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $senderPost->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn($idSender);

        $receiver = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $receiver->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn($idReceiver);

        $category = new \MicheleAngioni\MessageBoard\Models\Category();
        $category->name = 'category';
        $category->default_pic = 'filename.jpg';
        $category->save();

        $mbGateway = $this->app->make('MicheleAngioni\MessageBoard\MbGateway');
        $post = $mbGateway->createPost($receiver, $senderPost, $category->getKey(), 'text', false);

        // Like the Post

        $mbGateway->createLike($idUserLiker, $post->getKey(), 'post');

        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');
        $this->assertEquals(2, $notificationService->getNotifications()->count());

        $likeNotification = $notificationService->getNotifications()->last();
        $this->assertEquals($idUserLiker, $likeNotification->from_id);
        $this->assertEquals($idReceiver, $likeNotification->to_id);
        $this->assertEquals(0, $likeNotification->read);
        $this->assertEquals('', $likeNotification->pic_url);
    }

    public function testNotificationCreatedAfterNewCommentLike()
    {
        // Following path for base path is needed by Purifier package
        $this->app['path.base'] .= '/../../../..';
        $this->app['config']['ma_messageboard.model'] = 'UserEvent';
        $this->app['config']['ma_messageboard.posts_per_page'] = 20;
        $this->app['config']['ma_messageboard.user_named_route'] = 'user';

        $idUserLiker = 1;
        $idReceiver = 2;
        $idSender = 3;

        // Create a new Post with a Category

        $sender = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $sender->shouldReceive('getPrimaryId')
            ->andReturn($idSender);

        $receiver = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $receiver->shouldReceive('getPrimaryId')
            ->andReturn($idReceiver);

        $category = new \MicheleAngioni\MessageBoard\Models\Category();
        $category->name = 'category';
        $category->default_pic = 'filename.jpg';
        $category->save();

        $mbGateway = $this->app->make('MicheleAngioni\MessageBoard\MbGateway');
        $post = $mbGateway->createPost($receiver, $sender, $category->getKey(), 'text', false);

        // Create a Comment

        $comment = $mbGateway->createComment($receiver, $post->getKey(), 'text', false);

        // Like the Comment

        $mbGateway->createLike($idUserLiker, $comment->getKey(), 'comment');

        $notificationService = $this->app->make('MicheleAngioni\MessageBoard\Services\NotificationService');
        $this->assertEquals(2, $notificationService->getNotifications()->count());

        $likeNotification = $notificationService->getNotifications()->last();
        $this->assertEquals($idUserLiker, $likeNotification->from_id);
        $this->assertEquals($idReceiver, $likeNotification->to_id);
        $this->assertEquals(0, $likeNotification->read);
        $this->assertEquals('', $likeNotification->pic_url);
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
class UserEvent extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
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
