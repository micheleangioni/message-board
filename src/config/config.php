<?php

return array(

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
    | Default pictures path
    |--------------------------------------------------------------------------
    |
    | This is the relative path where models' pictures are saved on the server.
    | A NULL value means no pictures will be used.
    | N.B. do NOT put directory separator at the end of the path.
    |
    */

    'pic_path' => null,

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
    |
    */

    'notifications' => [

        /**
         * If set to true, notifications are created after new mb posts, comments
         * and likes events are fired.
         */
        'after_mb_events' => false,

        /**
         * If set to true, the pic url will be added to the notifications.
         * Model must implement the MbModelNotifableInterface
         */
        'use_model_pics' => false

    ]

);
