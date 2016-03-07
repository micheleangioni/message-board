<?php

namespace MicheleAngioni\MessageBoard\Repos;

use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\NotificationRepositoryInterface;
use MicheleAngioni\MessageBoard\Models\Notification;

class EloquentNotificationRepository extends AbstractEloquentRepository implements NotificationRepositoryInterface
{
    protected $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    /**
     * Set input user notifications as read if older than input datetime 'Y-m-d H:i:s' .
     *
     * @param  int  $idUser
     * @param  string  $datetime
     *
     * @return bool
     */
    public function setUserNotificationsAsReadByDatetime($idUser, $datetime)
    {
        $this->model->where('user_id', $idUser)
                    ->where('created_at', '<=', $datetime)
                    ->update(['read' => true]);

        return true;
    }

    /**
     * Delete all old notification older than input datetime 'Y-m-d H:i:s' .
     *
     * @param  int  $datetime
     * @return mixed
     */
    public function deleteOldNotifications($datetime)
    {
        return $this->model->where('created_at', '<', $datetime)->delete();
    }
}
