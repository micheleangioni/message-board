<?php

class MbMessageBoardServiceTest extends Orchestra\Testbench\TestCase
{
    protected $categoryRepo;

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
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);
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
            'Mews\Purifier\PurifierServiceProvider',
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

    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'UTC';
    }


	public function testCreateCodedPost()
	{
        $this->mockRepositories();

        $this->app->bind('html', function($app) {
            $html = $this->mock('html');
            $html->shouldReceive('linkRoute')
                ->once()
                ->andReturn('link');

            return $html;
        });

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');
        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $user->shouldReceive('getPrimaryId')
            ->twice()
            ->andReturn(1);

        $user->shouldReceive('getUsername')
            ->once()
            ->andReturn('username');

        $this->postRepo->shouldReceive('create')
            ->once()
            ->andReturn($post);

        Event::shouldReceive('fire')->once();

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Post',
            $mbService->createCodedPost($user, null, 'code', []));
    }

    public function testCreatePost()
    {
        $this->mockRepositories();

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');
        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $user->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(1);

        $this->postRepo->shouldReceive('create')
            ->once()
            ->andReturn($post);

        $poster = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $poster->shouldReceive('getPrimaryId')
            ->once()
            ->andReturn(2);

        $poster->shouldReceive('isBanned')
            ->once()
            ->andReturn(false);

        Event::shouldReceive('fire')->once();

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Post',
            $mbService->createPost($user, $poster, null, 'text'));
    }

    /**
     * @expectedException \MicheleAngioni\MessageBoard\Exceptions\UserIsBannedException
     */
    public function testCreatePostByBannedUser()
    {
        $this->mockRepositories();

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $poster = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $poster->shouldReceive('isBanned')
            ->once()
            ->andReturn(true);

        $poster->shouldReceive('getUsername')
            ->once()
            ->andReturn('Username');

        $mbService->createPost($user, $poster, null, 'text');
    }

    public function testGetPost()
    {
        $this->mockRepositories();

        $idPost = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idPost)
            ->andReturn(true);

        $this->assertTrue($mbService->getPost($idPost));
    }

    public function testUpdatePost()
    {
        $this->mockRepositories();

        $idPost = 10;
        $newText = 'New Text';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idPost)
            ->andReturn($post);


        $post->shouldReceive('updateText')
            ->once()
            ->with($newText)
            ->andReturn(true);

        $this->assertTrue($mbService->updatePost($idPost, $newText));
    }

    public function testDeletePost()
    {
        $this->mockRepositories();

        $idPost = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idPost)
            ->andReturn($post);

        $post->shouldReceive('delete')
            ->once()
            ->with($idPost)
            ->andReturn(true);

        Event::shouldReceive('fire')->once();

        $this->assertTrue($mbService->deletePost($idPost));
    }

    public function testCreateComment()
    {
        $this->mockRepositories();

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');
        $post->shouldReceive('getAttribute')
            ->andReturn(null);

        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');

        $user->shouldReceive('getPrimaryId')
            ->andReturn(1);

        $user->shouldReceive('isBanned')
            ->once()
            ->andReturn(false);

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->andReturn($post);

        $this->commentRepo->shouldReceive('create')
            ->once()
            ->andReturn($comment);

        Event::shouldReceive('fire')->once();

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Comment',
            $mbService->createComment($user, 1, 'ciao'));
    }

    /**
     * @expectedException \MicheleAngioni\MessageBoard\Exceptions\UserIsBannedException
     */
    public function testCreateCommentByBannedUser()
    {
        $this->mockRepositories();

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');

        $user->shouldReceive('getUsername')
            ->once()
            ->andReturn('Username');

        $user->shouldReceive('isBanned')
            ->once()
            ->andReturn(true);

        $mbService->createComment($user, 1, 'ciao');
    }

    public function testGetComment()
    {
        $this->mockRepositories();

        $idComment = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idComment)
            ->andReturn(true);

        $this->assertTrue($mbService->getComment($idComment));
    }

    public function testUpdateComment()
    {
        $this->mockRepositories();

        $idComment = 10;
        $newText = 'New Text';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idComment)
            ->andReturn($comment);

        $comment->shouldReceive('updateText')
            ->once()
            ->with($newText)
            ->andReturn(true);

        $this->assertTrue($mbService->updateComment($idComment, $newText));
    }

    public function testDeleteComment()
    {
        $this->mockRepositories();

        $idComment = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');

        $this->commentRepo->shouldReceive('findOrFail')
            ->once()
            ->with($idComment)
            ->andReturn($comment);

        $comment->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        Event::shouldReceive('fire')->once();

        $this->assertTrue($mbService->deleteComment($idComment));
    }

    public function testCreatePostLikeIfNotAlreadyLiked()
    {
        $this->mockRepositories();

        $idUser = 1;
        $likableEntityId = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');
        $like = $this->mock('MicheleAngioni\MessageBoard\Models\Like');

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
            ->andReturn($like);

        $post->shouldReceive('likes')
            ->once()
            ->andReturn($likesMock);

        Event::shouldReceive('fire')->once();

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Like',
            $mbService->createLike($idUser, $likableEntityId, 'post'));
    }

    public function testCreatePostLikeAlreadyLiked()
    {
        $this->mockRepositories();

        $idUser = 1;
        $likableEntityId = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $post = $this->mock('MicheleAngioni\MessageBoard\Models\Post');

        $this->postRepo->shouldReceive('findOrFail')
            ->once()
            ->with($likableEntityId)
            ->andReturn($post);

        $this->likeRepo->shouldReceive('getUserEntityLike')
            ->once()
            ->with($post, $idUser)
            ->andReturn(true);

        $this->assertTrue($mbService->createLike($idUser, $likableEntityId, 'post'));
    }

    public function testCreateCommentLikeIfNotAlreadyLiked()
    {
        $this->mockRepositories();

        $idUser = 1;
        $likableEntityId = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');
        $comment = $this->mock('MicheleAngioni\MessageBoard\Models\Comment');
        $like = $this->mock('MicheleAngioni\MessageBoard\Models\Like');

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
            ->andReturn($like);

        $comment->shouldReceive('likes')
            ->once()
            ->andReturn($likesMock);

        Event::shouldReceive('fire')->once();

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Like',
            $mbService->createLike($idUser, $likableEntityId, 'comment'));
    }

    public function testDeletePostLike()
    {
        $this->mockRepositories();

        $idUser = 1;
        $likableEntityId = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

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

        $this->assertTrue($mbService->deleteLike($idUser, $likableEntityId, 'post'));
    }

    public function testDeleteCommentLike()
    {
        $this->mockRepositories();

        $idUser = 1;
        $likableEntityId = 10;

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

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

        $this->assertTrue($mbService->deleteLike($idUser, $likableEntityId, 'comment'));
    }


    // <<< --- INTEGRATION TESTS --- >>>

    public function testCodedPostWithCategory()
    {
        $app = $this->app;
        $appPath = $app['path.base'];
        $this->app['path.base'] .= '/../../../..';

        $app['path.base'] = $appPath;
        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $this->app->bind('html', function($app) {
            $html = $this->mock('html');
            $html->shouldReceive('linkRoute')
                ->once()
                ->andReturn('linkToUser');

            return $html;
        });

        $mbGateway = $this->app->make('MicheleAngioni\MessageBoard\MbGateway');

        $category = $mbGateway->createCategory('Category', null, false);

        $user = new User;
        $user->id = 1;
        $user->save();

        $post = $mbGateway->createCodedPost($user, $category->getKey(), 'user', ['user' => 'username']);

        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Post', $post);
        $this->assertEquals(trans('ma_messageboard::messageboard.user', ['user' => 'linkToUser']), $post->getText());
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testGetNotAccessiblePost()
    {
        $mbGateway = $this->app->make('MicheleAngioni\MessageBoard\MbGateway');

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $categoryRepo = $this->app->make('MicheleAngioni\MessageBoard\Contracts\CategoryRepositoryInterface');
        $category = $categoryRepo->create(['name' => 'Name', 'private' => true]);

        $post = $mbGateway->createPost($user2, null, $category->getKey(), 'text', true);

        $mbGateway->getPost($post->getKey(), $user);
    }

    public function testBanUser()
    {
        $app = $this->app;
        $appPath = $app['path.base'];
        $this->app['path.base'] .= '/../../../..';

        $app['path.base'] = $appPath;
        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = new User;

        $user->id = 1;
        $user->save();

        $this->assertFalse($user->isBanned());

        // Ban the User

        $mbService->banUser($user, 3, $reason = 'Ban');
        $user = User::find(1);
        $this->assertTrue($user->isBanned());
        $this->assertEquals(1, count($user->mbBans));

        // Ban again the User

        $mbService->banUser($user, 5, $reason = 'Ban New');
        $user = User::find(1);
        $this->assertEquals(1, count($user->mbBans));
        $ban = $user->getBan();
        $this->assertEquals($ban->getReason(), 'Ban New');

        $this->assertEquals(date('Y-m-d', strtotime('8 days')), $ban->getUntil());
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testDeletePostByOtherUser()
    {
        $app = $this->app;

        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $post = $user->mbPosts()->create([
            'category_id' => null,
            'poster_id' => $user->id,
            'text' => 'text'
        ]);

        $mbService->deletePost($post->id, $user2);
    }

    public function testDeletePostByAdmin()
    {
        $app = $this->app;

        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $roleRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository');
        $role = $roleRepo->findOrFail(1);

        $user2->attachMbRole($role);

        $post = $user->mbPosts()->create([
            'category_id' => null,
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ]);

        $this->assertTrue($mbService->deletePost($post->id, $user2));
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testDeleteCommentByOtherUser()
    {
        $app = $this->app;

        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $post = $user->mbPosts()->create([
            'category_id' => null,
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ]);

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'text' => 'text'
        ]);

        $mbService->deleteComment($comment->id, $user2);
    }

    public function testDeleteCommentByAdmin()
    {
        $app = $this->app;

        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = new User;
        $user->id = 1;
        $user->save();

        $user2 = new User;
        $user2->id = 2;
        $user2->save();

        $roleRepo = $this->app->make('MicheleAngioni\MessageBoard\Repos\EloquentRoleRepository');
        $role = $roleRepo->findOrFail(1);

        $user2->attachMbRole($role);

        $post = $user->mbPosts()->create([
            'category_id' => null,
            'user_id' => $user->id,
            'poster_id' => $user->id,
            'text' => 'text'
        ]);

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'text' => 'text'
        ]);

        $this->assertTrue($mbService->deleteComment($comment->id, $user2));
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
        $post->category_id = null;
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
        $post2->category_id = null;
        $post2->text = 'text';
        $post2->created_at = $postdatetime;
        $post2->save();

        $comment2 = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment2->post_id = 2;
        $comment2->user_id = 2;
        $comment2->text = 'text';
        $comment2->created_at = $comment2Datetime;
        $comment2->save();

        $post3 = new MicheleAngioni\MessageBoard\Models\Post;
        $post3->id = 3;
        $post3->user_id = 1;
        $post3->category_id = 10;
        $post3->text = 'text';
        $post3->created_at = $postdatetime;
        $post3->save();

        $category = new MicheleAngioni\MessageBoard\Models\Category;
        $category->id = 10;
        $category->name = 'category';
        $category->created_at = $postdatetime;
        $category->save();

        $app = $this->app;

        $app['config']['ma_messageboard.model'] = 'User';
        $app['config']['ma_messageboard.posts_per_page'] = 20;
        $app['config']['ma_messageboard.user_named_route'] = 'user';

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $queryMock = $this->mock('QueryMock');
        $queryMock->shouldReceive('update')->andReturn(true);

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');
        $user->mbLastView = true;
        $user->shouldReceive('getPrimaryId')->andReturn(1);
        $user->shouldReceive('mbLastView')->andReturn($queryMock);

        $posts = $mbService->getOrderedUserPosts($user, false);
        $this->assertEquals(2, $posts[0]->id);

        $posts = $mbService->getOrderedUserPosts($user, 10);
        $this->assertEquals(3, $posts[0]->id);
        $this->assertEquals(1, $posts->count());

        $posts = $mbService->getOrderedUserPosts($user, 'category');
        $this->assertEquals(3, $posts[0]->id);
        $this->assertEquals(1, $posts->count());
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
        $post->category_id = null;
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

        $app = $this->app;

        Config::shouldReceive('get')->with('ma_messageboard.model')->andReturn('User');

        $mbService = $this->app->make('MicheleAngioni\MessageBoard\Services\MessageBoardService');

        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');
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

        $posts = $mbService->getOrderedUserPosts($user, 'all', 1,  20, true, true);

        $this->assertGreaterThanOrEqual(0, strpos($posts[0]->text,'<?'));
        $this->assertGreaterThanOrEqual(0, strpos($posts[0]->comments[0]->text,'<?'));
    }
    */

    public function mockRepositories()
    {
        $this->categoryRepo = $this->mock('MicheleAngioni\MessageBoard\Contracts\CategoryRepositoryInterface');
        $this->commentRepo = $this->mock('MicheleAngioni\MessageBoard\Contracts\CommentRepositoryInterface');
        $this->likeRepo = $this->mock('MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface');
        $this->postRepo = $this->mock('MicheleAngioni\MessageBoard\Contracts\PostRepositoryInterface');
        $this->purifier = $this->mock('MicheleAngioni\MessageBoard\Contracts\PurifierInterface');
        $this->presenter = $this->mock('MicheleAngioni\Support\Presenters\Presenter');
        $this->viewRepo = $this->mock('MicheleAngioni\MessageBoard\Contracts\ViewRepositoryInterface');
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
class User extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
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
