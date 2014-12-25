<?php

class PostModelTest extends TestCase {

	public function testSinglePostGetChildDatetimeAttribute()
	{
        $datetime = date('Y-m-d H:i:s');

        $post = App::make('TopGames\MessageBoard\Models\Post');
        $post->created_at = $datetime;

        $this->assertEquals($datetime, $post->child_datetime);
    }

    public function testPostWith1CommentGetChildDatetimeAttribute()
    {
        $postdatetime = date('Y-m-d H:i:s');
        $commentDatetime = date("Y-m-d H:i:s", strtotime('+1 minute'));

        $post = App::make('TopGames\MessageBoard\Models\Post');
        $post->id = 1;
        $post->user_id = 1;
        $post->post_type = 'public_mess';
        $post->text = 'text';
        $post->created_at = $postdatetime;
        $post->save();

        $comment = $post->comments()->create(['user_id' => 1, 'text' => 'text']);
        $comment->created_at = $commentDatetime;
        $comment->save();

        $this->assertEquals($commentDatetime, $post->child_datetime);
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