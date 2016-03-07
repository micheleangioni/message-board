<?php

namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Contracts\CommentEventInterface;
use MicheleAngioni\MessageBoard\Models\Comment;
use Illuminate\Queue\SerializesModels;

class CommentDelete implements CommentEventInterface
{
	use SerializesModels;

    /**
     * @var Comment
     */
    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * {inheritdoc}
     */
    public function getComment()
    {
        return $this->comment;
    }
}
