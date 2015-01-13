<?php

class PostModelTest extends Orchestra\Testbench\TestCase {

    /*
    protected function getPackageProviders($app)
    {
        return ['MicheleAngioni\MessageBoard\MessageBoardServiceProvider'];
    }
    */

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.model', 'User');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

	public function testSinglePostGetChildDatetimeAttribute()
	{
        date_default_timezone_set('UTC');
        $datetime = date('Y-m-d H:i:s');

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->created_at = $datetime;
        $post->comments = new Illuminate\Support\Collection;

        $this->assertEquals($datetime, $post->child_datetime);
    }

    public function testPostWith1CommentGetChildDatetimeAttribute()
    {
        date_default_timezone_set('UTC');
        $postdatetime = date('Y-m-d H:i:s');
        $commentDatetime = date("Y-m-d H:i:s", strtotime('+1 minute'));

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->id = 1;
        $post->user_id = 1;
        $post->post_type = 'public_mess';
        $post->text = 'text';
        $post->created_at = $postdatetime;

        $comment = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment->post_id = 1;
        $comment->text = 'text';
        $comment->created_at = $commentDatetime;

        $post->comments = new Illuminate\Support\Collection;
        $post->comments->push($comment);

        $this->assertEquals($commentDatetime, $post->child_datetime);
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
