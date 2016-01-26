<?php namespace MicheleAngioni\MessageBoard\Listeners;

use MicheleAngioni\MessageBoard\Services\NotificationService;
use UnexpectedValueException;

class MessageBoardEventHandler {

    protected $notificationService;


    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function onPostCreate($event)
    {
        // Check if the Post has been created on the own author's message board

        if(!($event->post->getAuthorId() == $event->post->getOwnerId())) {
            if($event->post->getAuthor()) {
                $username = $event->post->getAuthor()->getUsername();
                $picUrl = 'images/profiles/' . $event->post->getAuthor()->getProfileImageFilename();
                $text = trans('notifications.mb_post_new', ['username' => $username]);
            }
            else {
                //TODO ADD CHOICE BASED ON FROM_TYPE COLUMN OF POST
                $username = 'Default Username';
                $picUrl = 'images/profiles/default.png';
                $text = substr($event->post->text, 0, min(strlen($event->post->text), 100));
            }

            $this->notificationService->sendNotification(
                $event->post->getOwnerId(),
                null,
                $event->post->getAuthorId(),
                'mb_post_new',
                $text,
                $picUrl,
                $this->getUserNamedRoute($event->post->getOwnerId()),
                [
                    'url_reference' => $this->getUrlReference('post', $event->post->id)
                ]
            );
        }

        return true;
    }

    public function onCommentCreate($event)
    {
        // Check if the Comment has been created on a Post owned by another user (i.e. stays in the mb of another user)

        if(!($event->comment->getAuthorId() == $event->comment->getOwnerId())) {
            if($event->comment->getAuthor()) {
                $username = $event->comment->getAuthor()->getUsername();
                $picUrl = 'images/profiles/' . $event->comment->getAuthor()->getProfileImageFilename();
                $text = trans('notifications.mb_comment_new', ['username' => $username]);
            }
            else {
                //TODO ADD CHOICE BASED ON FROM_TYPE COLUMN OF COMMENT'S POST
                $username = 'Default Username';
                $picUrl = 'images/profiles/default.png';
                $text = substr($event->post->text, 0, min(strlen($event->post->text), 100));
            }

            $this->notificationService->sendNotification(
                $event->comment->getOwnerId(),
                null,
                $event->comment->getAuthorId(),
                'mb_comment_new',
                $text,
                $picUrl,
                $this->getUserNamedRoute($event->comment->getOwnerId()),
                [
                    'url_reference' => $this->getUrlReference('comment', $event->comment->id)
                ]
            );
        }

        return true;
    }

    public function onLikeCreate($event)
    {
        // See if the like is on a Post or a Comment

        $likableType = $event->like->getLikableType();

        if(strpos($likableType, 'Post') !== false) {
            // Check if the user likes an own post

            if(!($event->like->likable->getAuthorId() == $event->like->user->id)) {
                if($event->like->user) {
                    $username = $event->like->user->getUsername();
                    $picUrl = 'images/profiles/' . $event->like->user->getProfileImageFilename();
                    $text = trans('notifications.mb_like_post', ['username' => $username]);
                }
                else {
                    $username = null;
                    $picUrl = null;
                    $text = null;
                }

                $this->notificationService->sendNotification(
                    $event->like->likable->getOwnerId(),
                    null,
                    $event->like->likable->getAuthorId(),
                    'mb_like_post',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($event->like->likable->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('post', $event->post->id)
                    ]
                );
            }
        }
        elseif(strpos($likableType, 'Comment') !== false) {
            // Check if the user likes an own comment

            if(!($event->like->likable->getAuthorId() == $event->like->user->id)) {
                if($event->like->user) {
                    $username = $event->like->user->getUsername();
                    $picUrl = 'images/profiles/' . $event->like->user->getProfileImageFilename();
                    $text = trans('notifications.mb_like_comment', ['username' => $username]);
                }
                else {
                    $username = null;
                    $picUrl = null;
                    $text = null;
                }

                $this->notificationService->sendNotification(
                    $event->like->likable->getOwnerId(),
                    null,
                    $event->like->likable->getAuthorId(),
                    'mb_like_comment',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($event->like->likable->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('comment', $event->comment->id)
                    ]
                );
            }
        }
        else {
            throw new UnexpectedValueException("UnexpectedValueException in ".__METHOD__.' at line '.__LINE__.': Invalid likable type :'. e($likableType));
        }

        return true;
    }

    /**
     * @param  $event
     * @return bool
     */
    protected function isPostOwnedByAuthor($event)
    {
        if($event->post->getAuthorId() == $event->post->getOwnerId()) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Return the url for input user/entity.
     * Return an input string if no route is set in the config file.
     *
     * @param  int  $id
     * @return string
     */
    protected function getUserNamedRoute($id)
    {
        try {
            $url = route(config('ma_messageboard.user_named_route'),  $id);
        } catch (\Exception $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * Return the url reference for input prefix and id.
     *
     * @param  string  $prefix
     * @param  int  $id
     *
     * @return string
     */
    protected function getUrlReference($prefix, $id)
    {
        return '#' . $prefix . '-' . $id;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        // Check if the notifications on new post, comments and likes are enabled in the config file

        if(config('ma_messageboard.notifications.after_mb_events')) {
            $events->listen('MicheleAngioni\MessageBoard\Events\CommentCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onCommentCreate');

            $events->listen('MicheleAngioni\MessageBoard\Events\LikeCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onLikeCreate');

            $events->listen('MicheleAngioni\MessageBoard\Events\PostCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onPostCreate');
        }
    }

}
