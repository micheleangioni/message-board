<?php namespace MicheleAngioni\MessageBoard\Events;

use MicheleAngioni\MessageBoard\Models\Notification;
use Illuminate\Queue\SerializesModels;

class NotificationCreate {

	use SerializesModels;

    /**
     * @var Notification
     */
    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

}
