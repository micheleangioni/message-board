<?php namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Models\Comment;
use Illuminate\Queue\SerializesModels;

class CommentDelete {

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

}
