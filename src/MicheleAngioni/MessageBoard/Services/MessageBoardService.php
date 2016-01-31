<?php namespace MicheleAngioni\MessageBoard\Services;

use Helpers;
use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Contracts\CommentRepositoryInterface as CommentRepo;
use MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface as LikeRepo;
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

class MessageBoardService {

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
                         PurifierInterface $purifier, ViewRepo $viewRepo)
    {
        $this->commentRepo = $commentRepo;

        $this->likeRepo = $likeRepo;

        $this->postsPerPage = config('ma_messageboard.posts_per_page');

        $this->postRepo = $postRepo;

        $this->presenter = $presenter;

        $this->purifier = $purifier;

        $this->viewRepo = $viewRepo;
    }

    /**
     * Create the coded text of the mb post.
     * Keys in the messageboard.php lang file will be used for localization.
     *
     * @param  string  $code
     * @param  MbUserInterface  $user
     * @param  array  $variables
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getCodedPostText($code, MbUserInterface $user = null, array $variables = [])
    {
        if(!$code) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': No input code.');
        }

        // Set user attribute, is set

        if($user) {
            $variables['user'] =  $this->getUserLink($user);
        }

        return $this->mbText = trans('ma_messageboard::messageboard.'."$code", $variables);
    }

    /**
     * Create a new Post from a coded text.
     *
     * @param  MbUserInterface  $user
     * @param  int|null  $categoryId
     * @param  string  $code
     * @param  array  $attributes
     *
     * @return Post
     */
    public function createCodedPost(MbUserInterface $user, $categoryId = null, $code, array $attributes = [])
    {
        $data = [
            'category_id' => $categoryId,
            'user_id' => $user->getPrimaryId(),
            'text' => $this->getCodedPostText($code, $user, $attributes)
        ];

        $post = $this->postRepo->create($data);

        // Fire event
        Event::fire(new PostCreate($post));

        return $post;
    }

    /**
     * Create a new Post. By default, check if the poster is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  MbUserInterface|null  $poster
     * @param  int|null  $categoryId
     * @param  string  $text
     * @param  bool  $banCheck
     * @throws InvalidArgumentException
     * @throws PermissionsException
     *
     * @return Post
     */
    public function createPost(MbUserInterface $user, MbUserInterface $poster = null, $categoryId = null, $text, $banCheck = true)
    {
        // Check if the poster is banned and take poster id
        if($poster) {
            if($banCheck) {
                if($poster->isBanned()) {
                    throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $poster->getUsername() . ' is currently banned and cannot create a new post.');
                }
            }

            $posterId = $poster->getPrimaryId();
        }
        else {
            $posterId = null;
        }

        $data = [
            'category_id' => $categoryId,
            'user_id' => $user->getPrimaryId(),
            'poster_id' => $posterId,
            'text' => $text
        ];

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
     * Updated text of input Post.
     * If a User is provided as third argument, a check will be performed if he/she can delete the Post.
     * Return bool on success.
     *
     * @param  int  $idPost
     * @param  string  $text
     * @param  MbUserInterface  $user
     * @throws ModelNotFoundException
     *
     * @return Post
     */
    public function updatePost($idPost, $text, MbUserInterface $user = null)
    {
        $post = $this->postRepo->findOrFail($idPost);

        if($user) {
            if(!$this->userCanDeleteEntity($user, $post)) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot updated post with id $idPost.");
            }
        }

        $post->updateText($text);

        return true;
    }

    /**
     * Delete input Post. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Post.
     *
     * @param  int  $idPost
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
        $post->delete($idPost);

        return true;
    }

    /**
     * Create a new Comment. Check if the user is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  int  $postId
     * @param  string  $text
     * @param  bool  $banCheck = true
     *
     * @return Comment
     */
    public function createComment(MbUserInterface $user, $postId, $text, $banCheck = true)
    {
        // Check if the user is banned
        if($banCheck) {
            if($user->isBanned()) {
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
     * Updated text of input Comment. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Comment.
     *
     * @param  int  $idComment
     * @param  string  $text
     * @param  MbUserInterface  $user
     * @throws ModelNotFoundException
     *
     * @return bool
     */
    public function updateComment($idComment, $text, MbUserInterface $user = null)
    {
        $comment = $this->getComment($idComment);

        if($user) {
            if(!$this->userCanDeleteEntity($user, $comment)) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot delete comment with id $idComment.");
            }
        }

        $comment->updateText($text);

        return true;
    }

    /**
     * Delete input Comment. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Comment.
     *
     * @param  int  $idComment
     * @param  MbUserInterface  $user
     * @throws ModelNotFoundException
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
        $comment->delete();

        return true;
    }

    /**
     * Check if input User can edit input Post/Comment.
     *
     * @param  MbUserInterface  $user
     * @param  Post|Comment  $entity
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
     * @param  Post|Comment  $entity
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
     * @param  int  $idUser
     * @param  int  $likableEntityId
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
     * @param  int  $idUser
     * @param  int  $likableEntityId
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
     * If $category is FALSE, no check will be made on categories. If set to NULL, only Posts with no categories
     *  will be retrieved. Otherwise $category can be the Category id or name.
     * If $private is NULL, no checks will be made. If TRUE/FALSE, only private/public posts will be retrieved.
     * If the user requesting the post is not the owner of the message board, it can be specified among the inputs.
     *
     * @param  MbUserInterface  $user
     * @param  int|string|bool|null  $category
     * @param  bool|null  $private
     * @param  int  $page = 1
     * @param  int  $limit = 2
     * @param  bool  $applyPresenter = false
     * @param  bool  $escapeText = false
     * @param  MbUserInterface|bool  $userVisiting
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function getOrderedUserPosts(MbUserInterface $user, $category = false, $private = null, $page = 1, $limit = 20,
                                        $applyPresenter = false, $escapeText = false, MbUserInterface $userVisiting = null)
    {
        if(!$limit) {
            $limit = $this->postsPerPage;
        }

        try {
            $posts = $this->postRepo->getOrderedPosts($user->getPrimaryId(), $category, $private, $page, $limit);
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

        // Check if an User is requesting his own Message Board, in case update his last view

        if(is_null($userVisiting)) {
            $this->updateUserLastView($user);
        }

        return $posts;
    }

    /**
     * Pass a Post or Comment model to the corresponding presenter and return it
     *
     * @param  MbUserInterface  $user
     * @param  Post|Comment  $model
     * @param  bool  $escapeText = false
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
     * @param  Collection  $collection
     * @param  bool  $escapeText = false
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
     * @param  int  $days
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
            $ban = $user->mbBans()->create([
                'reason' => $reason,
                'until' => Helpers::getDate(max($days,0)),
            ]);
        }

        // Fire Event
        Event::fire(new UserBanned($ban));

        return true;
    }

    /**
     * Return input user page link for the view.
     *
     * @param  MbUserInterface  $user
     * @return string
     */
    protected function getUserLink(MbUserInterface $user)
    {
        return link_to_route(config('ma_messageboard.user_named_route'), e($user->getUsername()), [$user->getPrimaryId()], []);
    }

}
