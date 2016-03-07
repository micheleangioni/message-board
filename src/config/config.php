<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | This is the Model used to send messages to and from.
    |
    */

    'model' => App\User::class,

    /*
    |--------------------------------------------------------------------------
    | Posts per page
    |--------------------------------------------------------------------------
    |
    | Default number of posts will be displayed per page.
    |
    */

    'posts_per_page' => 20,

    /*
    |--------------------------------------------------------------------------
    | Message Board User page named route
    |--------------------------------------------------------------------------
    |
    | This is the named route to the user page. It will use a GET parameter
    | as /named_route/{id} to select input user
    |
    */

    'user_named_route' => '',

    /*
    |--------------------------------------------------------------------------
    | Localization file name
    |--------------------------------------------------------------------------
    |
    | If you want to use your localization file for coded posts, just specify
    | the file name.
    |
    */

    'localization_file_name' => null,

    /*
    |--------------------------------------------------------------------------
    | MessageBoard Purifier Configuration
    |--------------------------------------------------------------------------
    |
    | This is the default configuration used by Html Purifier to escape
    | post and comment text.
    |
    */

    'mb_purifier_conf' => [
        'HTML.Doctype'             => 'XHTML 1.0 Strict',
        'HTML.Allowed'             => 'b,i,a[href|title]',
        'CSS.AllowedProperties'    => '',
        'AutoFormat.AutoParagraph' => false,
        'AutoFormat.RemoveEmpty'   => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | The following settings activate and customize the Notifications service.
    | If you do not use the Notificatons, you can leave them to their default
    | values.
    |
    */

    'notifications' => [

        /*
         * If set to true, notifications are created after new mb posts, comments
         * and likes events are fired.
         */
        'after_mb_events' => false,

        /*
         * If set to true, the pic url will be added to the notifications.
         * Model must implement the MbUserWithImageInterface.
         */
        'use_model_pic' => false,

        /*
         * This is the relative path where users pictures are saved on the server.
         * A NULL value means no pictures will be used.
         * N.B. do NOT put a directory separator at the end of the path.
         */

        'user_pic_path' => null,

        /*
         * This is the relative path where category pictures are saved on the server.
         * A NULL value means no pictures will be used.
         * N.B. do NOT put a directory separator at the end of the path.
         */
        'category_pic_path' => null,

        /*
         * Used to set the max length for notifications without a User sender.
         */
        'notification_max_length' => 100

    ],

    /*
    |--------------------------------------------------------------------------
    | APIs
    |--------------------------------------------------------------------------
    |
    | The following settings activate and customize Message Board APIs.
    |
    */

    'api' => [

        /**
         * By default, Message Board will use the tymon/jwt-auth token middlewares to authenticate and refresh tokens.
         * However, you can use your custom middlewares by changing the names in the array below.
         */
        'middlewares' => [
            'auth' => 'jwt.auth',
            'refresh' => 'jwt.refresh'
        ],

        /*
         * Set to TRUE to enable the authentication endpoints.
         * These endpoints are subject to versioning as well.
         */
        'authentication' => false,

        /*
         * Set to TRUE to enable the notification endpoints.
         * These endpoints are subject to versioning as well.
         */
        'notifications' => false,

        /*
         * Set to TRUE to enable v1 api endpoints.
         */
        'v1_enabled' => false,

        /*
         * The route prefix for API routes.
         */
        'api_prefix' => '/api',

        /**
         * By default API errors will be logged using the default Laravel Log class.
         */
        'log_errors' => true,

    ]

];
