<?php namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Contracts\LikeEventInterface;
use MicheleAngioni\MessageBoard\Models\Like;
use Illuminate\Queue\SerializesModels;

class LikeCreate implements LikeEventInterface {

	use SerializesModels;

    /**
     * @var Like
     */
    public $like;

    /**
     * Create a new event instance.
     */
    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    /**
     * {inheritdoc}
     */
    public function getLike()
    {
        return $this->like;
    }

}
