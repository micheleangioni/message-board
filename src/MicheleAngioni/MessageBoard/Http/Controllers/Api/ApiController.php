<?php

namespace MicheleAngioni\MessageBoard\Http\Controllers\Api;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use Tymon\JWTAuth\JWTAuth;
use Response;

class ApiController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /*
     * 4xx Internal Message Board error codes
     */

    const CODE_WRONG_ARGS = 'MB-ERR-WRONG_ARGS';

    const CODE_NOT_FOUND = 'MB-ERR-NOT_FOUND';

    const CODE_UNAUTHORIZED = 'MB-ERR-UNAUTHORIZED';

    const CODE_FORBIDDEN = 'MB-ERR-FORBIDDEN';

    const CODE_USER_BANNED_ERROR = 'MB-ERR-USER_BANNED';

    const CODE_USER_NOT_FOUND_ERROR = 'MB-ERR-USER_NOT_FOUND';

    const CODE_USER_PERMISSIONS_ERROR = 'MB-ERR-USER_PERMISSIONS';

    /*
     * 5xx Internal Message Board error codes
     */

    const CODE_INTERNAL_ERROR = 'MB-ERR-INTERNAL';

    /**
     * @var JWTAuth
     */
    protected $auth;

    /**
     * @var Manager
     */
    protected $fractal;

    /**
     * @var int
     */
    protected $statusCode = 200;

    public function __construct(JWTAuth $auth, Manager $fractal)
    {
        $this->auth = $auth;

        $this->fractal = $fractal;
    }

    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param  int  $statusCode
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message, self::CODE_WRONG_ARGS);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param  string  $message
     * @return \Illuminate\Http\Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    protected function respondWithError($message, $errorCode)
    {
        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'status' => $this->statusCode,
                'details' => $message,
            ]
        ]);
    }

    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = Response::json($array, $this->statusCode, $headers);

        $response->header('Content-Type', 'application/json');

        return $response;
    }

    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }
}
