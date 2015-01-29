<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Models\Comment;
use MicheleAngioni\MessageBoard\Models\Like;
use MicheleAngioni\MessageBoard\Models\Post;
use MicheleAngioni\MessageBoard\PurifierInterface;
use MicheleAngioni\MessageBoard\Presenters\CommentPresenter;
use MicheleAngioni\MessageBoard\Presenters\PostPresenter;
use MicheleAngioni\MessageBoard\Repos\CommentRepositoryInterface as CommentRepo;
use MicheleAngioni\MessageBoard\Repos\LikeRepositoryInterface as LikeRepo;
use MicheleAngioni\MessageBoard\Repos\PostRepositoryInterface as PostRepo;
use MicheleAngioni\MessageBoard\Repos\RoleRepositoryInterface as RoleRepo;
use MicheleAngioni\MessageBoard\Repos\ViewRepositoryInterface as ViewRepo;
use MicheleAngioni\Support\Presenters\Presenter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

abstract class AbstractMbGateway implements MbGatewayInterface {

    /**
     * Allowed Post message types.
     *
     * @var array
     */
    protected $allowedMessageTypes = array();

    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    protected $commentRepo;

    /**
     * Entities a like can be associated to and relative repositories.
     *
     * @var array
     */
    protected $likableEntities = array(
        'comment' => 'commentRepo',
        'post' => 'postRepo'
    );

    protected $likeRepo;

    /**
     * Standard number of mb posts per page.
     *
     * @var int
     */
    protected $postsPerPage;

    protected $postRepo;

    protected $presenter;

    protected $purifier;

    protected $roleRepo;

    protected $viewRepo;


    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, Presenter $presenter,
                         PurifierInterface $purifier, RoleRepo $roleRepo, ViewRepo $viewRepo, $app = NULL)
    {
        $this->app = $app ?: app();

        $this->allowedMessageTypes = $this->app['config']->get('message-board::message_types');

        $this->commentRepo = $commentRepo;

        $this->likeRepo = $likeRepo;

        $this->postsPerPage = $this->app['config']->get('message-board::posts_per_page');

        $this->postRepo = $postRepo;

        $this->presenter = $presenter;

        $this->purifier = $purifier;

        $this->roleRepo = $roleRepo;

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
     * @param  string           $messageType
     * @param  string|bool      $code
     * @param  array            $attributes
     *
     * @return Post
     */
    public function createCodedPost(MbUserInterface $user, $messageType = 'public_mess', $code, array $attributes = array())
    {
        $data = array(
            'post_type' => $messageType,
            'user_id' => $user->getPrimaryId(),
        );

        // Take post text

        $data['text'] = $this->getCodedPostText($code, $user, $attributes);

        // Check if the message is private or not

        if($messageType == 'private_mess') {
            $data['is_read'] = 0;
        }
        else {
            $data['is_read'] = 1;
        }

        return $this->postRepo->create($data);
    }

    /**
     * Create a new Post.
     *
     * @param  MbUserInterface  $user
     * @param  int              $idPoster
     * @param  string           $messageType
     * @param  string           $text
     * @throws InvalidArgumentException
     *
     * @return Post
     */
    public function createPost(MbUserInterface $user, $idPoster = NULL, $messageType = 'public_mess', $text)
    {
        $data = array(
            'post_type' => $messageType,
            'user_id' => $user->getPrimaryId(),
            'poster_id' => $idPoster,
            'text' => $text,
        );

        // Check if the message type is allowed

        if(!in_array($messageType, $this->app['config']->get('message-board::message_types'))) {
            throw new InvalidArgumentException('Caught InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $messageType is not a valid message type.');
        }

        // Check if the message is private or not

        if($messageType == 'private_mess') {
            $data['is_read'] = 0;
        }
        else {
            $data['is_read'] = 1;
        }

        return $this->postRepo->create($data);
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
     * Delete input Post.
     *
     * @param  int  $idPost
     * @return bool
     */
    public function deletePost($idPost)
    {
        return $this->postRepo->destroy($idPost);
    }

    /**
     * Create a new Comment.
     *
     * @param  MbUserInterface  $user
     * @param  int              $postId
     * @param  string           $text
     *
     * @return Comment
     */
    public function createComment(MbUserInterface $user, $postId, $text)
    {
        return $this->commentRepo->create(array(
            'post_id' => $postId,
            'user_id' => $user->getPrimaryId(),
            'text' => $text,
        ));
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
     * Delete input Comment.
     *
     * @param  int  $idComment
     * @return bool
     */
    public function deleteComment($idComment)
    {
        return $this->commentRepo->destroy($idComment);
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
            return $entity->likes()->create(['user_id' => $idUser]);
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
     * @return bool
     */
    public function deleteLike($idUser, $likableEntityId, $likableEntity)
    {
        $entity = $this->{$this->likableEntities[$likableEntity]}->findOrFail($likableEntityId);

        $like = $this->likeRepo->getUserEntityLike($entity, $idUser);

        if($like) {
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
     * @param  MbUserInterface|bool  $userVisiting = NULL
     * @throws InvalidArgumentException
     *
     * @return Collection
     */
    public function getOrderedUserPosts(MbUserInterface $user, $messageType = 'all', $page = 1, $limit = 20,
                                        $applyPresenter = false, $escapeText = false, MbUserInterface $userVisiting = NULL)
    {
        if(!$limit) {
            $limit = $this->postsPerPage;
        }

        if( !in_array($messageType, array_merge(['all'], $this->allowedMessageTypes)) ) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $messageType is not a valid post type.');
        }

        $posts = $this->postRepo->getOrderedPosts($user->getPrimaryId(), $messageType, $page, $limit);

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

        if($collection[0] instanceof Comment) {
            return $this->presenter->collection($collection, new CommentPresenter($user, $escapeText, $this->purifier));
        }
        elseif($collection[0] instanceof Post) {

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
     * Return a collection of all available Roles.
     *
     * @return Collection
     */
    public function getRoles()
    {
        return $this->roleRepo->all();
    }

    /**
     * Return input user page link for the view.
     *
     * @param  MbUserInterface  $user
     * @return string
     */
    protected function getUserLink(MbUserInterface $user)
    {
        return link_to_route($this->app['config']->get('message-board::user_named_route'), e($user->getUsername()), [$user->getPrimaryId()], []);
    }

}
