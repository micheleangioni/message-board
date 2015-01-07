<?php

class AbstractGatewayTest extends Orchestra\Testbench\TestCase {

    protected $commentRepo;

    protected $likeRepo;

    protected $postRepo;

    protected $viewRepo;

    /*
    protected function getPackageProviders($app)
    {
        return ['MicheleAngioni\MessageBoard\MessageBoardServiceProvider'];
    }
    */

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

        $this->assertTrue($stub->createPost($user, NULL, 'public_mess', 'text'));
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

        $this->commentRepo->shouldReceive('create')
            ->once()
            ->andReturn(true);

        $this->assertTrue($stub->createComment($user, 1, 'ciao'));
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


    protected function getAbstractGatewayStub()
    {
        $this->commentRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\CommentRepositoryInterface');
        $this->likeRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\LikeRepositoryInterface');
        $this->postRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\PostRepositoryInterface');
        $this->viewRepo = $this->mock('MicheleAngioni\MessageBoard\Repos\ViewRepositoryInterface');
        $app = [];
        $app['config'] = $this->mock('config');
        $app['config']->shouldReceive('get')->andReturn(['public_mess','private_mess']);

        return $this->getMockForAbstractClass('MicheleAngioni\MessageBoard\AbstractMbGateway',
            [$this->commentRepo, $this->likeRepo, $this->postRepo, $this->viewRepo, $app]);
    }


    public function mock($class)
    {
        $mock = Mockery::mock($class);

        return $mock;
    }

    public function tearDown()
    {
        Mockery::close();
    }

}