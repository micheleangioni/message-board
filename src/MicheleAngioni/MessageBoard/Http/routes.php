<?php

// < ------ PATTERN DEFINITIONS ------ > //

Route::pattern('id', '[0-9]+');


// APIs

// v1

if(config('ma_messageboard.api.v1_enabled')) {
    // Authentication

    Route::group([
        'namespace' => 'MicheleAngioni\\MessageBoard\\Http\\Controllers\\Api\\v1',
        'prefix' => config('ma_messageboard.api.api_prefix') . '/v1'
    ], function() {

        // Authentication

        if (config('ma_messageboard.api.authentication')) {
            Route::post('auth', [
                'middleware' => 'GrahamCampbell\Throttle\Http\Middleware\ThrottleMiddleware:10,30', // 5 hits in 30 minutes
                'as' => 'mb.api.auth.authenticate',
                'uses' => 'AuthenticationController@authenticate'
            ]);
        }

        Route::group(['middleware' => ['jwt.auth', 'GrahamCampbell\Throttle\Http\Middleware\ThrottleMiddleware:150,5']], function () {
            // Logout
            Route::delete('auth', ['as' => 'mb.api.auth.logout', 'uses' => 'AuthenticationController@logout']);

            // Posts
            Route::get('posts', ['as' => 'mb.api.posts.index', 'uses' => 'PostController@index']);
            Route::post('posts', ['as' => 'mb.api.posts.store', 'uses' => 'PostController@store']);
            Route::delete('posts/{id}', ['as' => 'mb.api.posts.destroy', 'uses' => 'PostController@destroy']);
            Route::get('posts/{id}/comments', ['as' => 'mb.api.posts.commentsIndex', 'uses' => 'PostController@commentsIndex']);

            // Comments
            Route::post('comments', ['as' => 'mb.api.comments.store', 'uses' => 'CommentController@store']);
            Route::delete('comments/{id}', ['as' => 'mb.api.comments.destroy', 'uses' => 'CommentController@destroy']);

            if (config('ma_messageboard.api.notifications')) {
                // Notifications
                Route::get('notifications', ['as' => 'mb.api.notifications.index', 'uses' => 'NotificationController@index']);
                Route::put('notifications/{id}', ['as' => 'mb.api.notifications.read', 'uses' => 'NotificationController@read']);
            }
        });
    });
}
