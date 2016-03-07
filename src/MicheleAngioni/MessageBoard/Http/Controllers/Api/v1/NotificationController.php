<?php

namespace MicheleAngioni\MessageBoard\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use MicheleAngioni\MessageBoard\Http\Controllers\Api\ApiController;
use MicheleAngioni\MessageBoard\MbGateway;
use MicheleAngioni\MessageBoard\Services\NotificationService;
use MicheleAngioni\MessageBoard\Transformers\NotificationTransformer;
use Tymon\JWTAuth\JWTAuth;
use Log;

class NotificationController extends ApiController
{
    /*
     * Internal Message Board error codes
     */

    const CODE_INVALID_EXTRA_DATA_ERROR = 'MB-ERR-INVALID_EXTRA_DATA';

    const CODE_READING_NOTIFICATIONS_ERROR = 'MB-ERR-READING_NOTIFICATIONS';

    const CODE_RETRIEVING_NOTIFICATIONS_ERROR = 'MB-ERR-RETRIEVING_NOTIFICATIONS';

    const CODE_USER_NOTIFICATION_NOT_FOUND_ERROR = 'MB-ERR-USER_NOTIFICATION_NOT_FOUND';

    /**
     * @var MbGateway
     */
    protected $mbGateway;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * PostController constructor.
     *
     * @param  JWTAuth  $auth
     * @param  Manager  $fractal
     * @param  MbGateway  $mbGateway
     * @param  NotificationService $notificationService
     */
	function __construct(JWTAuth $auth, Manager $fractal, MbGateway $mbGateway, NotificationService $notificationService)
	{
        $this->mbGateway = $mbGateway;

        $this->notificationService = $notificationService;

        parent::__construct($auth, $fractal);
	}

    /**
     * Return the Notifications of the User.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'limit' => 'integer|min:1|max:100'
        ]);

        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $notifications = $this->notificationService->getUserNotifications($user->getPrimaryId(), $request->get('limit'));
        } catch (\Exception $e) {
            if(config(config('ma_messageboard.api.log_errors'))) {
                Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
            }

            $this->setStatusCode(500);
            return $this->respondWithError('Internal error retrieving user notifications. The error has been logged and will be fixed as soon as possible.',
                self::CODE_RETRIEVING_NOTIFICATIONS_ERROR);
        }

        return $this->respondWithCollection($notifications, new NotificationTransformer);
    }

    /**
     * Read a Notification.
     *
     * @param  int  $idNotification
     * @param  Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function read($idNotification, Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $user->readNotification($idNotification);
        } catch (\Exception $e) {
            if($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $this->setStatusCode(404);
                return $this->respondWithError('The requested notification has not been found.',
                    self::CODE_USER_NOTIFICATION_NOT_FOUND_ERROR);
            }
            else {
                if(config(config('ma_messageboard.api.log_errors'))) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error trying to read a notification. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_READING_NOTIFICATIONS_ERROR);
            }
        }

        return response()->json([]);
    }

    /**
     * Read all Notifications of the User.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function readAll(Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $user->readAllNotifications();
        } catch (\Exception $e) {
            if(config(config('ma_messageboard.api.log_errors'))) {
                Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
            }

            $this->setStatusCode(500);
            return $this->respondWithError('Internal error trying to read user notifications. The error has been logged and will be fixed as soon as possible.',
                self::CODE_READING_NOTIFICATIONS_ERROR);
        }

        return response()->json([]);
    }
}
