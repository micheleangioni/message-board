<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Message Types
    |--------------------------------------------------------------------------
    |
    | List of the different message types which can be posted in the message
    | board. Default values are public_mess and private_mess, but new ones
    | can be added.
    |
    */

    'message_types' => array(
        'public_mess',
        'private_mess'
    ),

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
    | User page named route
    |--------------------------------------------------------------------------
    |
    | This is the named route to the user page. It will use a GET parameter
    | as /named_route/{id} to select input user
    |
    */

    'user_named_route' => '',


    /*
    |--------------------------------------------------------------------------
    | MessageBoard Purifier Configuration
    |--------------------------------------------------------------------------
    |
    | This is the default configuration used by Html Purifier to escape
    | post and comment text.
    |
    */

    'mb_purifier_conf' => array(
        'HTML.Doctype'             => 'XHTML 1.0 Strict',
        'HTML.Allowed'             => 'b,i,a[href|title]',
        'CSS.AllowedProperties'    => '',
        'AutoFormat.AutoParagraph' => false,
        'AutoFormat.RemoveEmpty'   => true,
    ),
);
