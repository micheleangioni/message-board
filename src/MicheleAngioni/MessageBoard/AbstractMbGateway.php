<?php namespace MicheleAngioni\MessageBoard;

use Helpers;
use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Contracts\CommentRepositoryInterface as CommentRepo;
use MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface as LikeRepo;
use MicheleAngioni\MessageBoard\Contracts\MbGatewayInterface;
use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Contracts\PostRepositoryInterface as PostRepo;
use MicheleAngioni\MessageBoard\Contracts\PurifierInterface;
use MicheleAngioni\MessageBoard\Contracts\ViewRepositoryInterface as ViewRepo;
use MicheleAngioni\MessageBoard\Events\CommentCreate;
use MicheleAngioni\MessageBoard\Events\CommentDelete;
use MicheleAngioni\MessageBoard\Events\LikeCreate;
use MicheleAngioni\MessageBoard\Events\LikeDestroy;
use MicheleAngioni\MessageBoard\Events\PostCreate;
use MicheleAngioni\MessageBoard\Events\PostDelete;
use MicheleAngioni\MessageBoard\Events\UserBanned;
use MicheleAngioni\MessageBoard\Exceptions\InvalidMessageTypeException;
use MicheleAngioni\MessageBoard\Models\Comment;
use MicheleAngioni\MessageBoard\Models\Like;
use MicheleAngioni\MessageBoard\Models\Post;
use MicheleAngioni\MessageBoard\Presenters\CommentPresenter;
use MicheleAngioni\MessageBoard\Presenters\PostPresenter;
use MicheleAngioni\Support\Presenters\Presenter;
use Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use MicheleAngioni\Support\Exceptions\PermissionsException;
use RuntimeException;

abstract class AbstractMbGateway implements MbGatewayInterface {

    /**
     * Allowed Post message types.
     *
     * @var array
     */
    protected $allowedMessageTypes = [];

    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var CommentRepo
     */
    protected $commentRepo;

    /**
     * Entities a like can be associated to and relative repositories.
     *
     * @var array
     */
    protected $likableEntities = [
        'comment' => 'commentRepo',
        'post' => 'postRepo'
    ];

    /**
     * @var LikeRepo
     */
    protected $likeRepo;

    /**
     * Standard number of mb posts per page.
     *
     * @var int
     */
    protected $postsPerPage;

    /**
     * @var PostRepo
     */
    protected $postRepo;

    protected $presenter;

    protected $purifier;

    /**
     * @var ViewRepo
     */
    protected $viewRepo;


    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, Presenter $presenter,
                         PurifierInterface $purifier, ViewRepo $viewRepo, $app = null)
    {
        $this->app = $app ?: app();

        $this->allowedMessageTypes = $this->app['config']->get('ma_messageboard.message_types');

        $this->commentRepo = $commentRepo;

        $this->likeRepo = $likeRepo;

        $this->postsPerPage = $this->app['config']->get('ma_messageboard.posts_per_page');

        $this->postRepo = $postRepo;

        $this->presenter = $presenter;

        $this->purifier = $purifier;

        $this->viewRepo = $viewRepo;
    }

    /**
     * Create the text of the Post that will be sent.
     */
    abstract protected function getCodedPostText($code, MbUserInterface $user, array $attributes);

    /**
     * Create a new Post from a coded text.
     *
     * @param  MbUserInterface  $user
     * @param  string  $messageType
     * @param  string|bool  $code
     * @param  array  $attributes
     *
     * @return Post
     */
    public function createCodedPost(MbUserInterface $user, $messageType = 'public_mess', $code, array $attributes = [])
    {
        $data = [
            'post_type' => $messageType,
            'user_id' => $user->getPrimaryId()
        ];

        // Take post text

        $data['text'] = $this->getCodedPostText($code, $user, $attributes);

        // Check if the message type is allowed

        if(!$this->isMessageTypeAllowed($messageType)) {
            throw new InvalidMessageTypeException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $messageType is not a valid message type.');
        }

        // Check if the message is to be set private or not

        if($messageType == 'private_mess') {
            $data['is_read'] = 0;
        }
        else {
            $data['is_read'] = 1;
        }

        $post = $this->postRepo->create($data);

        // Fire event
        Event::fire(new PostCreate($post));

        return $post;
    }

    /**
     * Create a new Post. By default, check if the poster is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  MbUserInterface|null  $poster = null
     * @param  string  $messageType = 'public_mess'
     * @param  string  $text
     * @param  bool  $banCheck = true
     * @throws InvalidArgumentException
     *
     * @return Post
     */
    public function createPost(MbUserInterface $user, MbUserInterface $poster = null, $messageType = 'public_mess', $text, $banCheck = true)
    {
        // Check if the poster is banned and take poster id
        if($poster) {
            if($banCheck) {
                if ($poster->isBanned()) {
                    throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $poster->getUsername() . ' is currently banned and cannot create a new post.');
                }
            }

            $posterId = $poster->getPrimaryId();
        }
        else {
            $posterId = null;
        }

        $data = [
            'post_type' => $messageType,
            'user_id' => $user->getPrimaryId(),
            'poster_id' => $posterId,
            'text' => $text
        ];

        // Check if the message type is allowed

        if(!$this->isMessageTypeAllowed($messageType)) {
            throw new InvalidMessageTypeException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $messageType is not a valid message type.');
        }

        // Check if the message is private or not

        if($messageType == 'private_mess') {
            $data['is_read'] = 0;
        }
        else {
            $data['is_read'] = 1;
        }

        $post = $this->postRepo->create($data);

        // Fire event
        Event::fire(new PostCreate($post));

        return $post;
    }

    /**
     * Retrieve and return input Post.
     *
     * @param  int  $idPost
     * @throws ModelNotFoundException
     *
     * @return Post
     */
    public function getPost($idPost)
    {
        return $this->postRepo->findOrFail($idPost);
    }

    /**
     * Delete input Post. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Post.
     *
     * @param  int              $idPost
     * @param  MbUserInterface  $user
     *
     * @return bool
     */
    public function deletePost($idPost, MbUserInterface $user = null)
    {
        $post = $this->getPost($idPost);

        if($user) {
            if(!$this->userCanDeleteEntity($user, $post)) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot delete post with id $idPost.");
            }
        }

        // Fire Event
        Event::fire(new PostDelete($post));

        // Delete Post
        $this->postRepo->destroy($idPost);

        return true;
    }

    /**
     * Create a new Comment. Check if the user is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  int              $postId
     * @param  string  $text
     * @param  bool             $banCheck = true
     *
     * @return Comment
     */
    public function createComment(MbUserInterface $user, $postId, $text, $banCheck = true)
    {
        // Check if the user is banned
        if($banCheck) {
            if ($user->isBanned()) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . ' is currently banned and cannot create a new comment.');
            }
        }

        $comment = $this->commentRepo->create([
            'post_id' => $postId,
            'user_id' => $user->getPrimaryId(),
            'text' => $text,
        ]);

        // Fire Event
        Event::fire(new CommentCreate($comment));

        return $comment;
    }

    /**
     * Retrieve and return input Comment.
     *
     * @param  int  $idComment
     * @throws ModelNotFoundException
     *
     * @return Comment
     */
    public function getComment($idComment)
    {
        return $this->commentRepo->findOrFail($idComment);
    }

    /**
     * Delete input Comment. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Comment.
     *
     * @param  int  $idComment
     * @param  MbUserInterface  $user
     *
     * @return bool
     */
    public function deleteComment($idComment, MbUserInterface $user = null)
    {
        $comment = $this->getComment($idComment);

        if($user) {
            if(!$this->userCanDeleteEntity($user, $comment)) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot delete comment with id $idComment.");
            }
        }

        // Fire Event
        Event::fire(new CommentDelete($comment));

        // Delete Comment
        $this->commentRepo->destroy($idComment);

        return true;
    }

    /**
     * Check if input User can edit input Post/Comment.
     *
     * @param  MbUserInterface  $user
     * @param  Post|Comment     $entity
     *
     * @return bool
     */
    public function userCanEditEntity(MbUserInterface $user, $entity)
    {
        // Check if the user is banned
        if($user->isBanned()) {
            return false;
        }

        // Check if the user owns the entity
        if($entity instanceof Post) {
            if($entity->user_id == $user->getPrimaryId() || $entity->poster_id  == $user->getPrimaryId()) {
                return true;
            }
        }
        elseif($entity instanceof Comment) {
            if($entity->user_id == $user->getPrimaryId()) {
                return true;
            }
        }
        else {
            throw new InvalidArgumentException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $entity must be an instance of Post or Comment.');
        }

        // The user does not own the entity. Last chance to be able to edit it is to have the permission to edit not owned entities
        if($user->canMb('Edit Posts')) {
            return true;
        }

        return false;
    }

    /**
     * Check if input User can delete input Post/Comment.
     *
     * @param  MbUserInterface  $user
     * @param  Post|Comment     $entity
     *
     * @return bool
     */
    public function userCanDeleteEntity(MbUserInterface $user, $entity)
    {
        // Check if the user owns the entity
        if($entity instanceof Post) {
            if($entity->user_id == $user->getPrimaryId() || $entity->poster_id  == $user->getPrimaryId()) {
                return true;
            }
        }
        elseif($entity instanceof Comment) {
            if($entity->user_id == $user->getPrimaryId()) {
                return true;
            }
        }
        else {
            throw new InvalidArgumentException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $entity must be an instance of Post or Comment.');
        }

        // The user does not own the entity. Last chance to be able to edit it is to have the permission to edit not owned entities
        if($user->canMb('Delete Posts')) {
            return true;
        }

        return false;
    }

    /**
     * Assign a new Like to input entity and user.
     *
     * @param  int     $idUser
     * @param  int     $likableEntityId
     * @param  string  $likableEntity
     * @throws InvalidArgumentException
     *
     * @return Like
     */
    public function createLike($idUser, $likableEntityId, $likableEntity)
    {
        if(!isset($this->likableEntities[$likableEntity])) {
            throw new InvalidArgumentException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $likableEntity is not a valid entity to be liked.');
        }

        // Check if input entity is already liked by the input user, if not, create a new one

        $entity = $this->{$this->likableEntities[$likableEntity]}->findOrFail($likableEntityId);

        $like = $this->likeRepo->getUserEntityLike($entity, $idUser);

        if(!$like) {
            $like =  $entity->likes()->create(['user_id' => $idUser]);

            // Fire Event
            Event::fire(new LikeCreate($like));

            return $like;
        }
        else {
            return $like;
        }
    }

    /**
     * Delete input like. Return true on success.
     *
     * @param  int     $idUser
     * @param  int     $likableEntityId
     * @param  string  $likableEntity
     *
     * @return bool
     */
    public function deleteLike($idUser, $likableEntityId, $likableEntity)
    {
        $entity = $this->{$this->likableEntities[$likableEntity]}->findOrFail($likableEntityId);

        $like = $this->likeRepo->getUserEntityLike($entity, $idUser);

        if($like) {
            // Fire Event
            Event::fire(new LikeDestroy($like));

            $like->delete();
        }

        return true;
    }

    /**
     * Update input user last view.
     *
     * @param  MbUserInterface  $user
     */
    public function updateUserLastView(MbUserInterface $user)
    {
        if(!$user->mbLastView) {
       $user->mbLastView()->create(['datetime' => date('Y-m-d H:i:s')]);
        }
        else {
       $user->mbLastView()->update(['datetime' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Return posts of input type on the input user mb, ordered by post AND comment datetime.
     * Optionally the posts can be passed to the presenter and the text escaped.
     * If the user requesting the post is not the owner of the message board, it can be specified among the iputs
     *
     * @param  MbUserInterface  $user
     * @param  string  $messageType = 'all'
     * @param  int     $page = 1
     * @param  int     $limit = 2
     * @param  bool    $applyPresenter = false
     * @param  bool    $escapeText = false
     * @param  MbUserInterface|bool  $userVisiting = null
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function getOrderedUserPosts(MbUserInterface $user, $messageType = 'all', $page = 1, $limit = 20,
                                        $applyPresenter = false, $escapeText = false, MbUserInterface $userVisiting = null)
    {
        if(!$limit) {
            $limit = $this->postsPerPage;
        }

        if( !in_array($messageType, array_merge(['all'], $this->allowedMessageTypes)) ) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $messageType is not a valid post type.');
        }

        try {
            $posts = $this->postRepo->getOrderedPosts($user->getPrimaryId(), $messageType, $page, $limit);
        } catch (\Exception $e) {
            throw new RuntimeException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.':'. $e->getMessage());
        }

        if($applyPresenter) {
            if($userVisiting) {
                $posts = $this->presentCollection($userVisiting, $posts, $escapeText);
            }
            else {
                $posts = $this->presentCollection($user, $posts, $escapeText);
            }
        }

        return $posts;
    }

    /**
     * Pass a Post or Comment model to the corresponding presenter and return it
     *
     * @param  MbUserInterface  $user
     * @param  Post|Comment     $model
     * @param  bool             $escapeText = false
     * @throws InvalidArgumentException
     *
     * @return PostPresenter|CommentPresenter
     */
    public function presentModel(MbUserInterface $user, $model, $escapeText = false)
    {
        if($model instanceof Post) {
            $model = $this->presenter->model($model, new PostPresenter($user, $escapeText, $this->purifier));
        }
        elseif($model instanceof Comment) {
            $model = $this->presenter->model($model, new CommentPresenter($user, $escapeText, $this->purifier));
        }
        else {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': input model should be an instance of Post or Comment.');
        }

        return $model;
    }

    /**
     * Pass a Post or Comment collection to the corresponding presenter and return it
     *
     * @param  MbUserInterface  $user
     * @param  Collection       $collection
     * @param  bool             $escapeText = false
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function presentCollection(MbUserInterface $user, Collection $collection, $escapeText = false)
    {
        if($collection->count() == 0) {
            return $collection;
        }

        if($collection->first() instanceof Comment) {
            return $this->presenter->collection($collection, new CommentPresenter($user, $escapeText, $this->purifier));
        }
        elseif($collection->first() instanceof Post) {

            $newCollection = new Collection;

            foreach($collection as $key => $post){
                $newCollection[$key] = $this->presenter->model($post, new PostPresenter($user, $escapeText, $this->purifier));
                $newCollection[$key]->comments = $this->presentCollection($user, $post->comments, $escapeText);
            }

            return $newCollection;
        }
        else {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': input collection should contain Post or Comment models.');
        }
    }

    /**
     * Ban input user. In the user is already banned, extend the banning days.
     * Return true on success.
     *
     * @param  MbUserInterface  $user
     * @param  int              $days
     * @param  string  $reason = ''
     *
     * @return bool
     */
    public function banUser(MbUserInterface $user, $days, $reason = '')
    {
        // Check if input user is already banned
        if($user->isBanned()) {
            // Extend the current ban

            $ban = $user->mbBans->first();

            $currentBanDays = Helpers::daysBetweenDates(Helpers::getDate(), $ban->until);

            $ban->until = max($currentBanDays + $days,0);
            $ban->save();
        }
        else {
            $ban = $user->mbBans()->create(array(
                'reason' => $reason,
                'until' => Helpers::getDate(max($days,0)),
            ));
        }

        // Fire Event
        Event::fire(new UserBanned($ban));

        return true;
    }

    /**
     * Check if input $messageType is allowed. In no message_types are defined in the config files, all types are allowed.
     *
     * @param  string  $messageType
     * @return bool
     */
    protected function isMessageTypeAllowed($messageType)
    {
        if(count($this->app['config']->get('ma_messageboard.message_types')) === 0) {
            return true;
        }

        if(in_array($messageType, $this->app['config']->get('ma_messageboard.message_types'))) {
            return true;
        }

        return false;
    }

    /**
     * Return input user page link for the view.
     *
     * @param  MbUserInterface  $user
     * @return string
     */
    protected function getUserLink(MbUserInterface $user)
    {
        return link_to_route($this->app['config']->get('ma_messageboard.user_named_route'), e($user->getUsername()), [$user->getPrimaryId()], []);
    }

}
