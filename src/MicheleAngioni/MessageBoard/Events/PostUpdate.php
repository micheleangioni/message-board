<?php namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Models\Post;
use Illuminate\Queue\SerializesModels;

class PostUpdate {

	use SerializesModels;

    /**
     * @var Post
     */
    public $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

}
