<?php

namespace TopGames\MessageBoard;

use Illuminate\Support\Collection;
use TopGames\MessageBoard\Models\Comment;
use TopGames\MessageBoard\Models\Like;
use TopGames\MessageBoard\Models\Post;
use TopGames\MessageBoard\Repos\CommentRepositoryInterface as CommentRepo;
use TopGames\MessageBoard\Repos\LikeRepositoryInterface as LikeRepo;
use TopGames\MessageBoard\Repos\PostRepositoryInterface as PostRepo;
use TopGames\MessageBoard\Repos\ViewRepositoryInterface as ViewRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

abstract class AbstractMbGateway implements MbGatewayInterface {

    /**
     * Allowed Post message types.
     *
     * @var array
     */
    protected $allowedMessageTypes = array('private_mess', 'public_mess');

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

    protected $viewRepo;


    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, ViewRepo $viewRepo)
    {
        $this->app = app();

        $this->commentRepo = $commentRepo;

        $this->likeRepo = $likeRepo;

        $this->postsPerPage = $this->app['config']->get('message-board::posts_per_page');

        $this->postRepo = $postRepo;

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
     * It also set the child_datetime post attribute.
     *
     * @param  MbUserInterface  $user
     * @param  string           $messageType
     * @param  int              $page
     * @param  int|bool         $limit
     *
     * @return Collection
     */
    public function getOrderedUserPosts(MbUserInterface $user, $messageType = 'all', $page = 1, $limit = false)
    {
        if(!$limit) {
            $limit = $this->postsPerPage;
        }

        return $this->postRepo->getOrderedPosts($user->getPrimaryId(), $messageType, $page, $limit);
    }

    /**
     * Return input user page link for the view.
     *
     * @param  MbUserInterface  $user
     * @return string
     */
    protected function getUserLink(MbUserInterface $user)
    {
        return link_to_route($this->app['config']->get('message-board::user_named_route'), e($user->getUsername()), $parameters = array($user->getPrimaryId()), $attributes = array());
    }

}