<?php

namespace MicheleAngioni\MessageBoard\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use MicheleAngioni\MessageBoard\Http\Controllers\Api\ApiController;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;

class AuthenticationController extends ApiController
{
    /*
     * Internal Message Board error codes
     */

    const CODE_WRONG_CREDENTIALS_ERROR = 'MB-ERR-WRONG_CREDENTIALS';

    const CODE_GENERATING_TOKEN_ERROR = 'MB-ERR-GENERATING_TOKEN';

    /**
     * PostController constructor.
     *
     * @param  JWTAuth  $auth
     * @param  Manager  $fractal
     */
	function __construct(JWTAuth $auth, Manager $fractal)
	{
        parent::__construct($auth, $fractal);
	}

    /**
     * Authenticate input user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|max:128'
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if(!$token = $this->auth->attempt($credentials)) {
                $this->setStatusCode(401);
                return $this->respondWithError('Wrong credentials.',
                    self::CODE_WRONG_CREDENTIALS_ERROR);
            }
        } catch (JWTException $e) {
            if(config('ma_messageboard.api.log_errors')) {
                Log::error("Caught Exception in ".__METHOD__.' at line '.__LINE__." for user ". $request->json('email') .": {$e->getMessage()}");
            }

            $this->setStatusCode(500);
            return $this->respondWithError('Internal error generating the user token. The error has been logged and will be fixed as soon as possible.',
                self::CODE_GENERATING_TOKEN_ERROR);
        }

        return response()->json(compact('token'));
    }

    /**
     * Route used to refresh the used token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        $this->auth->setRequest($request);

        return response()->json([]);
    }

    /**
     * Logout an User.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->auth->setRequest($request)->parseToken();

        // Logout the User
        $this->auth->invalidate();

        response()->json();
    }
}
