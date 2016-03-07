<?php

namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Models\Ban;
use Illuminate\Queue\SerializesModels;

class UserBanned
{
	use SerializesModels;

    /**
     * @var Ban
     */
    public $ban;

    /**
     * Create a new event instance.
     */
    public function __construct(Ban $ban)
    {
        $this->ban = $ban;
    }
}
