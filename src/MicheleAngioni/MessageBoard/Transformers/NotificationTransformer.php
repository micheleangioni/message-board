<?php namespace MicheleAngioni\MessageBoard\Transformers;

use League\Fractal\TransformerAbstract;
use MicheleAngioni\MessageBoard\Models\Notification;

class NotificationTransformer extends TransformerAbstract
{
    /**
     * Turn input Player model into a generic array
     *
     * @param  Notification  $notification
     * @return array
     */
    public function transform(Notification $notification)
    {
        return [
            'id' => (int)$notification->getKey(),
            'fromId' => (int)$notification->getFromId(),
            'fromType' => $notification->getFromType(),
            'toId' => (int)$notification->getToId(),
            'type' => $notification->getType(),
            'text' => $notification->getText(),
            'picUrl' => $notification->getPicUrl(),
            'url' => $notification->getUrl(),
            'extra' => json_decode($notification->getExtra()),
            'isRead' => $notification->isRead(),
            'cleanText' => $notification->getCleanText(),
            'createdAt' => (string)$notification->getCreatedAt()
        ];
    }

}
