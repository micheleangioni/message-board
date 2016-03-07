<?php

namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Contracts\PostEventInterface;
use MicheleAngioni\MessageBoard\Models\Post;
use Illuminate\Queue\SerializesModels;

class PostDelete implements PostEventInterface
{
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

    /**
     * {inheritdoc}
     */
    public function getPost()
    {
        return $this->post;
    }
}
