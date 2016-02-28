<?php namespace MicheleAngioni\MessageBoard\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use MicheleAngioni\MessageBoard\Contracts\UserRepositoryInterface as UserRepo;
use MicheleAngioni\MessageBoard\Http\Controllers\Api\ApiController;
use MicheleAngioni\MessageBoard\MbGateway;
use MicheleAngioni\MessageBoard\Transformers\CommentTransformer;
use MicheleAngioni\MessageBoard\Transformers\PostTransformer;
use MicheleAngioni\Support\Presenters\Presenter;
use Tymon\JWTAuth\JWTAuth;
use Log;

class PostController extends ApiController {

    /*
     * Internal Message Board error codes
     */

    const CODE_RETRIEVING_POSTS_ERROR = 'MB-ERR-RETRIEVING_POSTS';

    const CODE_RETRIEVING_POST_COMMENTS_ERROR = 'MB-ERR-RETRIEVING_POST_COMMENTS';

    const CODE_DELETING_POSTS_ERROR = 'MB-ERR-DELETING_POSTS';

    /**
     * @var MbGateway
     */
    protected $mbGateway;

    /**
     * @var Presenter
     */
    protected $presenter;

    /**
     * @var UserRepo
     */
    protected $userRepo;

    /**
     * PostController constructor.
     *
     * @param  JWTAuth  $auth
     * @param  Manager  $fractal
     * @param  MbGateway  $mbGateway
     * @param  Presenter  $presenter
     * @param  UserRepo  $userRepo
     */
	function __construct(JWTAuth $auth, Manager $fractal, MbGateway $mbGateway, Presenter $presenter, UserRepo $userRepo)
	{
        $this->mbGateway = $mbGateway;

        $this->presenter = $presenter;

        $this->userRepo = $userRepo;

        parent::__construct($auth, $fractal);
	}

    /**
     * Return the Posts of a User.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Retrieve the authenticated User
        $userVisiting = $this->auth->setRequest($request)->parseToken()->toUser();

        $this->validate($request, [
            'idUser' => 'required|integer|min:1',
            'category' => 'alpha_space',
            'private' => 'boolean',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:20'
        ]);

        try {
            $user = $this->userRepo->findOrFail($request->json('idUser'));
        } catch (\Exception $e) {
            $this->setStatusCode(404);
            return $this->respondWithError('User not found.',
                self::CODE_USER_NOT_FOUND_ERROR);
        }

        try {
            $posts = $this->mbGateway->getOrderedUserPosts($user, $request->json('category'), $request->json('private'),
                                                           $request->json('page'), $request->json('limit'), true, true,
                                                           $userVisiting);
        } catch (\Exception $e) {
            if(config('ma_messageboard.api.log_errors')) {
                Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $userVisiting->getPrimaryId() .": {$e->getMessage()}");
            }

            $this->setStatusCode(500);
            return $this->respondWithError('Internal error retrieving user posts. The error has been logged and will be fixed as soon as possible.',
                self::CODE_RETRIEVING_POSTS_ERROR);
        }

        return $this->respondWithCollection($posts, new PostTransformer);
    }

    /**
     * Create a new Post.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Retrieve the authenticated User
        $userAuthor = $this->auth->setRequest($request)->parseToken()->toUser();

        $this->validate($request, [
            'idUser' => 'required|integer|min:1',
            'idCategory' => 'integer|min:1',
            'text' => 'required|alpha_complete|min:1'
        ]);

        try {
            $user = $this->userRepo->findOrFail($request->json('idUser'));
        } catch (\Exception $e) {
            $this->setStatusCode(404);
            return $this->respondWithError('User not found.',
                self::CODE_USER_NOT_FOUND_ERROR);
        }

        try {
            $this->mbGateway->createPost($user, $userAuthor, $request->json('idCategory'), $request->json('text'), true);
        } catch (\Exception $e) {
            if($e instanceof \MicheleAngioni\MessageBoard\Exceptions\UserIsBannedException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user is banned, so is unable to perform the required action.',
                    self::CODE_USER_BANNED_ERROR);
            }
            else {
                if(config('ma_messageboard.api.log_errors')) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $userAuthor->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error creating a new post. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_RETRIEVING_POSTS_ERROR);
            }
        }

        return response()->json([], 201);
    }

    /**
     * Delete input Post.
     *
     * @param  int  $idPost
     * @param  Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPost, Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $this->mbGateway->deletePost($idPost, $user);
        } catch (\Exception $e) {
            if($e instanceof \MicheleAngioni\Support\Exceptions\PermissionsException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user does not have the required permissions to delete input post.',
                    self::CODE_USER_PERMISSIONS_ERROR);
            }
            else {
                if(config('ma_messageboard.api.log_errors')) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error deleting a post. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_DELETING_POSTS_ERROR);
            }
        }

        return response()->json([]);
    }

    /**
     * Return the Comments belonging to input Post.
     *
     * @param  int  $idPost
     * @param  Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function commentsIndex($idPost, Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $post = $this->mbGateway->getPost($idPost, $user, true, true);
        } catch (\Exception $e) {
            if($e instanceof \MicheleAngioni\MessageBoard\Exceptions\UserIsBannedException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user is banned and cannot write new comments.',
                    self::CODE_USER_PERMISSIONS_ERROR);
            }
            elseif($e instanceof \MicheleAngioni\Support\Exceptions\PermissionsException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user cannot access to required post.',
                    self::CODE_USER_PERMISSIONS_ERROR);
            }
            else {
                if(config('ma_messageboard.api.log_errors')) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error retrieving post comments. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_RETRIEVING_POST_COMMENTS_ERROR);
            }
        }

        return $this->respondWithCollection($post->comments, new CommentTransformer);
    }

}
