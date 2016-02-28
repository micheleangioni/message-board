<?php namespace MicheleAngioni\MessageBoard\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use MicheleAngioni\MessageBoard\Http\Controllers\Api\ApiController;
use MicheleAngioni\MessageBoard\MbGateway;
use MicheleAngioni\MessageBoard\Transformers\PostTransformer;
use MicheleAngioni\MessageBoard\Contracts\UserRepositoryInterface as UserRepo;
use MicheleAngioni\Support\Presenters\Presenter;
use Tymon\JWTAuth\JWTAuth;
use Log;

class CommentController extends ApiController {

    /*
     * Internal Message Board error codes
     */

    const CODE_RETRIEVING_COMMENTS_ERROR = 'MB-ERR-RETRIEVING_COMMENTS';

    const CODE_DELETING_COMMENTS_ERROR = 'MB-ERR-DELETING_COMMENTS';

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
     * Create a new Comment.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        $this->validate($request, [
            'idPost' => 'required|integer|min:1',
            'text' => 'required|alpha_complete|min:1'
        ]);

        try {
            $this->mbGateway->createComment($user, $request->json('idPost'), $request->json('text'), true);
        } catch (\Exception $e) {
            if($e instanceof \MicheleAngioni\Support\Exceptions\PermissionsException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user is banned, so is unable to perform the required action.',
                    self::CODE_USER_BANNED_ERROR);
            }
            else {
                if(config(config('ma_messageboard.api.log_errors'))) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error creating a new post. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_RETRIEVING_COMMENTS_ERROR);
            }
        }

        return response()->json([], 201);
    }

    /**
     * Delete input Comment.
     *
     * @param  int  $idComment
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($idComment, Request $request)
    {
        // Retrieve the authenticated User
        $user = $this->auth->setRequest($request)->parseToken()->toUser();

        try {
            $this->mbGateway->deleteComment($idComment, $user);
        } catch (\Exception $e) {
            if($e instanceof \MicheleAngioni\Support\Exceptions\PermissionsException) {
                $this->setStatusCode(403);
                return $this->respondWithError('The user does not have the required permissions to delete input comment.',
                    self::CODE_USER_PERMISSIONS_ERROR);
            }
            else {
                if(config(config('ma_messageboard.api.log_errors'))) {
                    Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $user->getPrimaryId() .": {$e->getMessage()}");
                }

                $this->setStatusCode(500);
                return $this->respondWithError('Internal error deleting a comment. The error has been logged and will be fixed as soon as possible.',
                    self::CODE_DELETING_COMMENTS_ERROR);
            }
        }

        return response()->json([]);
    }

}
