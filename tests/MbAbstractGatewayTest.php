<?php

class MbAbstractGatewayTest extends Orchestra\Testbench\TestCase {

    protected $commentRepo;

    protected $likeRepo;

    protected $postRepo;

    protected $viewRepo;

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
            '--path' => '../tests/migrations',
        ));

        // Call seeding
        Artisan::call('db:seed', ['--class' => 'MessageBoardSeeder']);
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
            'Helpers' => 'MicheleAngioni\Support\Facades\Helpers',
        );
    }


	public function testCreateCodedPost()
	{
        $stub = $this->getAbstractGatewayStub();

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $user->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(1);

        $stub->expects($this->any())
             ->method('setCodedPostText')
             ->will($this->returnValue('text'));

        $this->postRepo->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $this->assertTrue($stub->createCodedPost($user, 'public_mess', 1, []));
    }

    public function testCreatePost()
    {
        $stub = $this->getAbstractGatewayStub();

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $user->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(1);

        $this->postRepo->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $poster = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $poster->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(2);

        $poster->shouldReceive('isBanned')
            ->once()
            ->andReturn(false);

        $this->assertTrue($stub->createPost($user, $poster, 'public_mess', 'text'));
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testCreatePostByBannedUser()
    {
        $stub = $this->getAbstractGatewayStub();

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $poster = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $poster->shouldReceive('isBanned')
            ->once()
            ->andReturn(true);

        $poster->shouldReceive('getUsername')
            ->once()
            ->andReturn('Username');

        $stub->createPost($user, $poster, 'public_mess', 'text');
    }

    public function testGetPost()
    {
        $isUser = 10;

        $stub = $this->getAbstractGatewayStub();

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($isUser)
            ->andReturn(true);

        $this->assertTrue($stub->getPost($isUser));
    }

    public function testDeletePost()
    {
        $isUser = 10;

        $stub = $this->getAbstractGatewayStub();

        $this->postRepo->shouldReceive('destroy')
            ->once()
            ->with($isUser)
            ->andReturn(true);

        $this->assertTrue($stub->deletePost($isUser));
    }

    public function testCreateComment()
    {
        $stub = $this->getAbstractGatewayStub();

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $user->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(1);

        $user->shouldReceive('isBanned')
            ->once()
            ->andReturn(false);

        $this->commentRepo->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $this->assertTrue($stub->createComment($user, 1, 'ciao'));
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testCreateCommentByBannedUser()
    {
        $stub = $this->getAbstractGatewayStub();

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');

        $user->shouldReceive('getUsername')
            ->once()
            ->andReturn('Username');

        $user->shouldReceive('isBanned')
            ->once()
            ->andReturn(true);

        $stub->createComment($user, 1, 'ciao');
    }

    public function testGetComment()
    {
        $idComment = 10;

        $stub = $this->getAbstractGatewayStub();

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idComment)
            ->andReturn(true);

        $this->assertTrue($stub->getComment($idComment));
    }

    public function testDeleteComment()
    {
        $idComment = 10;

        $stub = $this->getAbstractGatewayStub();

        $this->commentRepo->shouldReceive('destroy')
            ->once()
            ->with($idComment)
            ->andReturn(true);

        $this->assertTrue($stub->deleteComment($idComment));
    }

    public function testCreatePostLikeIfNotAlreadyLiked()
    {
        $idUser = 1;
        $likableEntityId = 10;

        $stub = $this->getAbstractGatewayStub();

        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($post);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($post, $idUser)
            ->andReturn(NULL);

        $likesMock = $this->mock('likesMock');

        $likesMock->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $post->shouldReceive('likes')
            ->once()
            ->andReturn($likesMock);

        $this->assertTrue($stub->createLike($idUser, $likableEntityId, 'post'));
    }

    public function testCreatePostLikeAlreadyLiked()
    {
        $idUser = 1;
        $likableEntityId = 10;

        $stub = $this->getAbstractGatewayStub();

        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($post);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($post, $idUser)
            ->andReturn(true);

        $this->assertTrue($stub->createLike($idUser, $likableEntityId, 'post'));
    }

    public function testCreateCommentLikeIfNotAlreadyLiked()
    {
        $idUser = 1;
        $likableEntityId = 10;

        $stub = $this->getAbstractGatewayStub();

        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($comment);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($comment, $idUser)
            ->andReturn(NULL);

        $likesMock = $this->mock('likesMock');

        $likesMock->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $comment->shouldReceive('likes')
            ->once()
            ->andReturn($likesMock);

        $this->assertTrue($stub->createLike($idUser, $likableEntityId, 'comment'));
    }

    public function testDeletePostLike()
    {
        $idUser = 1;
        $likableEntityId = 10;

        $stub = $this->getAbstractGatewayStub();

        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');
        $like = $this->mock('MicheleAngioni\MessageBoard\Models\Like');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($post);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($post, $idUser)
            ->andReturn($like);

        $like->shouldReceive('delete')
             ->once();

        $this->assertTrue($stub->deleteLike($idUser, $likableEntityId, 'post'));
    }

    public function testDeleteCommentLike()
    {
        $idUser = 1;
        $likableEntityId = 10;

        $stub = $this->getAbstractGatewayStub();

        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');
        $like = $this->mock('MicheleAngioni\MessageBoard\Models\Like');

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($comment);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($comment, $idUser)
            ->andReturn($like);

        $like->shouldReceive('delete')
            ->once();

        $this->assertTrue($stub->deleteLike($idUser, $likableEntityId, 'comment'));
    }


    // <<< --- INTEGRATION TESTS --- >>>

    public function testBanUser()
    {
        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
            $purifier, $viewRepo, $app);

        $user = new User;
        $user->id = 1;
        $user->save();

        $this->assertFalse($user->isBanned());

        $mbGateway->banUser($user, 3, $reason = 'Ban');
        $user = User::find(1);
        $this->assertTrue($user->isBanned());
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testDeletePostByOtherUser()
    {
        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
            $purifier, $viewRepo, $app);

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $post = $user->mbPosts()->create(array(
            'post_type' => 'public_mess',
            'poster_id' => $user->id,
            'text' => 'text'
        ));

        $mbGateway->deletePost($post->id, $user2);
    }

    public function testDeletePostByAdmin()
    {
        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
            $purifier, $viewRepo, $app);

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $roleRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository');
        $role = $roleRepo->findOrFail(1);

        $user2->attachMbRole($role);

        $post = $user->mbPosts()->create(array(
            'post_type' => 'public_mess',
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ));

        $this->assertTrue($mbGateway->deletePost($post->id, $user2));
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testDeleteCommentByOtherUser()
    {
        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
            $purifier, $viewRepo, $app);

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $post = $user->mbPosts()->create(array(
            'post_type' => 'public_mess',
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ));

        $comment = $post->comments()->create(array(
            'user_id' => $user->id,
            'text' => 'text'
        ));

        $mbGateway->deleteComment($comment->id, $user2);
    }

    public function testDeleteCommentByAdmin()
    {
        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
            $purifier, $viewRepo, $app);

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $roleRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository');
        $role = $roleRepo->findOrFail(1);

        $user2->attachMbRole($role);

        $post = $user->mbPosts()->create(array(
            'post_type' => 'public_mess',
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ));

        $comment = $post->comments()->create(array(
            'user_id' => $user->id,
            'text' => 'text'
        ));

        $this->assertTrue($mbGateway->deleteComment($comment->id, $user2));
    }

    public function testGetOrderedUserPosts()
    {
        date_default_timezone_set('UTC');
        $postdatetime = date('Y-m-d H:i:s');
        $commentDatetime = date("Y-m-d H:i:s", strtotime('+5 minute'));
        $comment2Datetime = date("Y-m-d H:i:s", strtotime('+10 minute'));

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->id = 1;
        $post->user_id = 1;
        $post->post_type = 'public_mess';
        $post->text = 'text';
        $post->created_at = $postdatetime;
        $post->save();

        $comment = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment->post_id = 1;
        $comment->user_id = 1;
        $comment->text = 'text';
        $comment->created_at = $commentDatetime;
        $comment->save();

        $post2 = new MicheleAngioni\MessageBoard\Models\Post;
        $post2->id = 2;
        $post2->user_id = 1;
        $post2->post_type = 'public_mess';
        $post2->text = 'text';
        $post2->created_at = $postdatetime;
        $post2->save();

        $comment2 = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment2->post_id = 2;
        $comment2->user_id = 2;
        $comment2->text = 'text';
        $comment2->created_at = $comment2Datetime;
        $comment2->save();

        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
                                                                $purifier, $viewRepo, $app);

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');
        $user->shouldReceive('getPrimaryId')->andReturn(1);

        $posts = $mbGateway->getOrderedUserPosts($user, 'all');

        $this->assertEquals(2, $posts[0]->id);
    }
    /*
    public function testGetOrderedUserPostsWithPresenter()
    {
        date_default_timezone_set('UTC');
        $postdatetime = date('Y-m-d H:i:s');
        $commentDatetime = date("Y-m-d H:i:s", strtotime('+5 minute'));

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->id = 1;
        $post->user_id = 1;
        $post->post_type = 'public_mess';
        $post->text = 'text <?';
        $post->created_at = $postdatetime;
        $post->save();

        $comment = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment->post_id = 1;
        $comment->user_id = 1;
        $comment->text = 'text <?';
        $comment->created_at = $commentDatetime;
        $comment->save();

        //unset($post); unset($comment); unset($comment2);

        $commentRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentCommentRepository');
        $likeRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentLikeRepository');
        $postRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentPostRepository');
        $presenter = $this->app->make('MicheleAngioni\Support\Presenters\Presenter');
        $purifier = $this->app->make('MicheleAngioni\MessageBoard\PurifierInterface');
        $viewRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentViewRepository');

        $app = $this->app;

        $mbGateway = new MicheleAngioni\MessageBoard\MbGateway($commentRepo, $likeRepo, $postRepo, $presenter,
                                                                $purifier, $viewRepo, $app);

        $user = $this->mock('MicheleAngioni\MessageBoard\MbUserInterface');
        $user->shouldReceive('getPrimaryId')->andReturn(1);

        $viewMock = $this->mock('MicheleAngioni\MessageBoard\Models\View');
        $viewMock
            ->shouldReceive('setAttribute')
            ->andReturn(true);
        $viewMock
            ->shouldReceive('getAttribute')
            ->andReturn(true);
        $viewMock->datetime = date("Y-m-d H:i:s", strtotime('+9 minute'));

        $user->mbLastView = $viewMock;

        $posts = $mbGateway->getOrderedUserPosts($user, 'all', 1,  20, true, true);

        $this->assertGreaterThanOrEqual(0, strpos($posts[0]->text,'<?'));
        $this->assertGreaterThanOrEqual(0, strpos($posts[0]->comments[0]->text,'<?'));
    }
    */

    protected function getAbstractGatewayStub()
    {
        $this->commentRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\CommentRepositoryInterface');
        $this->likeRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\LikeRepositoryInterface');
        $this->postRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\PostRepositoryInterface');
        $this->purifier = $this->mock('MicheleAngioni\MessageBoard\PurifierInterface');
        $this->presenter = $this->mock('MicheleAngioni\Support\Presenters\Presenter');
        $this->viewRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\ViewRepositoryInterface');

        Config::shouldReceive('get')->with('message-board::message_types')->andReturn(['public_mess','private_mess']);
        Config::shouldReceive('get')->with('message-board::posts_per_page')->andReturn(20);
        Config::shouldReceive('get')->with('message-board::user_named_route')->andReturn('user');

        return $this->getMockForAbstractClass('MicheleAngioni\MessageBoard\AbstractMbGateway',
            [$this->commentRepo, $this->likeRepo, $this->postRepo, $this->presenter, $this->purifier,
                $this->viewRepo]);
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
class User extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\MbUserInterface
{
    use MicheleAngioni\MessageBoard\MbTrait;

    public function getPrimaryId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
