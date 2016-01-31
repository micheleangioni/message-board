<?php namespace MicheleAngioni\MessageBoard\Listeners;

use Illuminate\Events\Dispatcher;
use MicheleAngioni\MessageBoard\Contracts\CommentEventInterface;
use MicheleAngioni\MessageBoard\Contracts\LikeEventInterface;
use MicheleAngioni\MessageBoard\Contracts\MbUserWithImageInterface;
use MicheleAngioni\MessageBoard\Contracts\PostEventInterface;
use MicheleAngioni\MessageBoard\Models\Post;
use MicheleAngioni\MessageBoard\Services\NotificationService;
use MicheleAngioni\MessageBoard\Exceptions\InvalidLikableTypeException;

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
                $picUrl = $this->getUserPicPath($post->getAuthor());
                $text = trans('notifications.mb_post_new', ['username' => $username]);
            }
            else {
                $picUrl = $this->getCategoryPicUrl($post);
                $text = substr($post->getText(), 0, min(strlen($post->getText()), config('ma_messageboard.notification_max_length')));
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
                $picUrl = $this->getUserPicPath($comment->getAuthor());
                $text = trans('notifications.mb_comment_new', ['username' => $username]);
            }
            else {
                $picUrl = '';
                $text = substr($comment->getText(), 0, min(strlen($comment->getText()), config('ma_messageboard.notification_max_length')));
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
            $likable = $like->getLikable();
            $likableType = $like->getLikableType();

            // See if the like is on a Post or a Comment

            if(strpos($likableType, 'Post') !== false) {
                if($like->getAuthor()) {
                    $username = $like->getAuthor()->getUsername();
                    $picUrl = $this->getUserPicPath($like->getAuthor());
                    $text = trans('notifications.mb_like_post', ['username' => $username]);
                } else {
                    $picUrl = '';
                    $text = '';
                }

                $this->notificationService->sendNotification(
                    $likable->getOwnerId(),
                    null,
                    $like->getAuthorId(),
                    'mb_like_post',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($likable->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('post', $like->getLikableId())
                    ]
                );
            } elseif(strpos($likableType, 'Comment') !== false) {
                if($like->getAuthor()) {
                    $username = $like->getAuthor()->getUsername();
                    $picUrl = $this->getUserPicPath($like->getAuthor());
                    $text = trans('notifications.mb_like_comment', ['username' => $username]);
                } else {
                    $picUrl = '';
                    $text = '';
                }

                $this->notificationService->sendNotification(
                    $likable->getOwnerId(),
                    null,
                    $like->getAuthorId(),
                    'mb_like_comment',
                    $text,
                    $picUrl,
                    $this->getUserNamedRoute($likable->getOwnerId()),
                    [
                        'url_reference' => $this->getUrlReference('comment', $like->getLikableId())
                    ]
                );
            } else {
                throw new InvalidLikableTypeException("UnexpectedValueException in " . __METHOD__ . ' at line ' . __LINE__ . ': Invalid likable type :' . e($likableType));
            }
        }

        return true;
    }

    /**
     * Return the pic file path of input User.
     * Return an empty string if user pics are disabled in config file.
     *
     * @param  MbUserWithImageInterface  $user
     * @return string
     */
    protected function getUserPicPath(MbUserWithImageInterface $user)
    {
        if(config('ma_messageboard.use_model_pic')) {
            $picPath = config('ma_messageboard.user_pic_path') . DIRECTORY_SEPARATOR . $user->getProfileImageFilename();
        }
        else {
            $picPath = '';
        }

        return $picPath;
    }

    /**
     * Return the Category pic path on input Post.
     * If the Post has no Category, return an empty string.
     *
     * @param  Post  $post
     * @return string
     */
    protected function getCategoryPicUrl(Post $post)
    {
        if(!$post->category) {
            return '';
        }

        return config('ma_messageboard.category_pic_path') . DIRECTORY_SEPARATOR . $post->category->getDefaultPic();
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
