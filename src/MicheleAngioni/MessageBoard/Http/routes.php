<?php

// < ------ PATTERN DEFINITIONS ------ > //


Route::pattern('id', '[0-9]+');
Route::pattern('id2', '[0-9]+');

Route::pattern('cou', '[0-9]+');
Route::pattern('reg', '[0-9]+');

Route::pattern('page', '[0-9]+');
Route::pattern('limit', '[0-9]+');

Route::pattern('age', '[0-9]+');


// APIs

// v1

if(config('ma_messageboard.api.v1_enabled')) {
    Route::group(['namespace' => 'MicheleAngioni\\MessageBoard\\Http\\Controllers\\Api\\v1', 'prefix' => '/api/v1', 'middleware' => 'jwt.auth'], function()
    {
        // Posts
        Route::get('posts', array('as' => 'mb.api.posts.index', 'uses' => 'PostController@index'));
        Route::post('posts', array('as' => 'mb.api.posts.store', 'uses' => 'PostController@store'));
        Route::delete('posts/{id}', array('as' => 'mb.api.posts.destroy', 'uses' => 'PostController@destroy'));
        Route::get('posts/{id}/comments', array('as' => 'mb.api.posts.commentsIndex', 'uses' => 'PostController@commentsIndex'));

        // Comments
        Route::post('comments', array('as' => 'mb.api.comments.store', 'uses' => 'CommentController@store'));
        Route::delete('comments/{id}', array('as' => 'mb.api.comments.destroy', 'uses' => 'CommentController@destroy'));

        if(config('ma_messageboard.api.notifications')) {
            // Notifications
            Route::get('notifications', array('as' => 'mb.api.notifications.index', 'uses' => 'NotificationController@index'));
            Route::put('notifications/{id}', array('as' => 'mb.api.notifications.read', 'uses' => 'NotificationController@read'));
        }
    });
}
