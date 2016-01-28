<?php

class MbPostModelTest extends Orchestra\Testbench\TestCase {

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

	public function testSinglePostGetChildDatetimeAttribute()
	{
        date_default_timezone_set('UTC');
        $datetime = date('Y-m-d H:i:s');

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->created_at = $datetime;
        $post->comments = new Illuminate\Support\Collection;

        $this->assertEquals($datetime, $post->childDatetime);
    }

    public function testPostWith1CommentGetChildDatetimeAttribute()
    {
        date_default_timezone_set('UTC');
        $postdatetime = date('Y-m-d H:i:s');
        $commentDatetime = date("Y-m-d H:i:s", strtotime('+1 minute'));

        $post = new MicheleAngioni\MessageBoard\Models\Post;
        $post->id = 1;
        $post->user_id = 1;
        $post->category_id = null;
        $post->text = 'text';
        $post->created_at = $postdatetime;

        $comment = new MicheleAngioni\MessageBoard\Models\Comment;
        $comment->post_id = 1;
        $comment->text = 'text';
        $comment->created_at = $commentDatetime;

        $post->comments = new Illuminate\Support\Collection;
        $post->comments->push($comment);

        $this->assertEquals($commentDatetime, $post->childDatetime);
    }

}
