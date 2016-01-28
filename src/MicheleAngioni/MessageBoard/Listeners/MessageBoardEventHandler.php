<?php namespace MicheleAngioni\MessageBoard\Listeners;

use Illuminate\Events\Dispatcher;
use MicheleAngioni\MessageBoard\Contracts\CommentEventInterface;
use MicheleAngioni\MessageBoard\Contracts\LikeEventInterface;
use MicheleAngioni\MessageBoard\Contracts\PostEventInterface;
use MicheleAngioni\MessageBoard\Services\NotificationService;
use UnexpectedValueException;

class MessageBoardEventHandler {

    protected $notificationService;


    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function onPostCreate(PostEventInterface $event)
    {
        // Check if the Post has been created on the own author's message board

        $post = $event->getPost();

        if(!$post->isInAuthorMb()) {
            if($post->getAuthor()) {
                $username = $post->getAuthor()->getUsername();
                $picUrl = config('ma_messageboard.pic_path') . DIRECTORY_SEPARATOR . $post->getAuthor()->getProfileImageFilename();
                $text = trans('notifications.mb_post_new', ['username' => $username]);
            }
            else {
                //TODO ADD CHOICE BASED ON FROM_TYPE COLUMN OF POST
                $username = 'Default Username';
                $picUrl = 'images/profiles/default.png';
                $text = substr($post->getText(), 0, min(strlen($post->getText()), 100));
            }

            $this->notificationService->sendNotification(
                $post->getOwnerId(),
                null,
                $post->getAuthorId(),
                'mb_post_new',
                $text,
                $picUrl,
                $this->getUserNamedRoute($post->getOwnerId()),
                [
                    'url_reference' => $this->getUrlReference('post', $post->id)
                ]
            );
        }

        return true;
    }

    public function onCommentCreate(CommentEventInterface $event)
    {
        $comment = $event->getComment();

        // Check if the Comment has been created on a Post owned by another user (i.e. stays in the mb of another user)

        if(!$comment->isInAuthorPost()) {
            if($comment->getAuthor()) {
                $username = $comment->getAuthor()->getUsername();
                $picUrl = 'images/profiles/' . $comment->getAuthor()->getProfileImageFilename();
                $text = trans('notifications.mb_comment_new', ['username' => $username]);
            }
            else {
                //TODO ADD CHOICE BASED ON FROM_TYPE COLUMN OF COMMENT'S POST
                $username = 'Default Username';
                $picUrl = 'images/profiles/default.png';
                $text = substr($comment->getText(), 0, min(strlen($comment->getText()), 100));
            }

            $this->notificationService->sendNotification(
                $comment->getOwnerId(),
                null,
                $comment->getAuthorId(),
                'mb_comment_new',
                $text,
                $picUrl,
                $this->getUserNamedRoute($comment->getOwnerId()),
                [
                    'url_reference' => $this->getUrlReference('comment', $comment->id)
                ]
            );
        }

        return true;
    }

    public function onLikeCreate(LikeEventInterface $event)
    {
        $like = $event->getLike();

        // Check if the user likes an own post

        if(!$like->authorLikesOwnEntity()) {
            $likableType = $like->getLikableType();

            // See if the like is on a Post or a Comment

            if(strpos($likableType, 'Post') !== false) {
                if($like->getAuthor()) {
                    $username = $like->getAuthor()->getUsername();
                    $picUrl = 'images/profiles/' . $like->getAuthor()->getProfileImageFilename();
                    $text = trans('notifications.mb_like_post', ['username' => $username]);
                } else {
                    $username = null;
                    $picUrl = null;
                    $text = null;
                }

                $this->notificationService->sendNotification(
                    $like->getLikable()->getOwnerId(),
                    null,
                    $like->getLikable()->getAuthorId(),
                    'mb_like_post',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($like->getLikable()->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('post', $event->getPost()->id)
                    ]
                );
            } elseif(strpos($likableType, 'Comment') !== false) {
                if($like->getAuthor()) {
                    $username = $like->getAuthor()->getUsername();
                    $picUrl = 'images/profiles/' . $like->getAuthor()->getProfileImageFilename();
                    $text = trans('notifications.mb_like_comment', ['username' => $username]);
                } else {
                    $username = null;
                    $picUrl = null;
                    $text = null;
                }

                $this->notificationService->sendNotification(
                    $like->getLikable()->getOwnerId(),
                    null,
                    $like->getLikable()->getAuthorId(),
                    'mb_like_comment',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($like->getLikable()->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('comment', $event->getComment()->id)
                    ]
                );
            } else {
                throw new UnexpectedValueException("UnexpectedValueException in " . __METHOD__ . ' at line ' . __LINE__ . ': Invalid likable type :' . e($likableType));
            }
        }

        return true;
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
     * @param  Dispatcher  $events
     * @return array
     */
    public function subscribe(Dispatcher $events)
    {
        // Check if the notifications on new post, comments and likes are enabled in the config file

        if(config('ma_messageboard.notifications.after_mb_events')) {
            $events->listen('MicheleAngioni\MessageBoard\Events\CommentCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onCommentCreate');

            $events->listen('MicheleAngioni\MessageBoard\Events\LikeCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onLikeCreate');

            $events->listen('MicheleAngioni\MessageBoard\Events\PostCreate', 'MicheleAngioni\MessageBoard\Listeners\MessageBoardEventHandler@onPostCreate');
        }
    }

}
