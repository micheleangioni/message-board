# MESSAGE BOARD

[![Build Status](https://travis-ci.org/micheleangioni/message-board.svg)](https://travis-ci.org/micheleangioni/message-board)
[![License](https://poser.pugx.org/michele-angioni/message-board/license.svg)](https://packagist.org/packages/michele-angioni/message-board) 
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5/small.png)](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5)

## Introduction

The Message Board is a [Laravel 4](http://laravel.com) package which assigns a message board to each User, where posts and comments can be posted. Social features like "likes" are included as well.

## Installation

Message Board can be installed through Composer, just include `"michele-angioni/message-board": "dev-master"` to your composer.json and run `composer update` or `composer install`.

## Configuration

The Message Board Service Provider must be added in the app.php config file, under the providers array

    'MicheleAngioni\MessageBoard\MessageBoardServiceProvider'

Then add the `MbTrait` to your User model, which has also to implement the `MbUserInterface` so that Message Board classes can type hint it

    <?php

    use MicheleAngioni\MessageBoard\MbTrait; // Message Board Trait
    use MicheleAngioni\MessageBoard\MbUserInterface;

    class User MbUserInterface {

        use MbTrait; // Message Board Trait

        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'tb_users';

        [...]

Even if not strictly needed, it is recommended to publish the Message Board conf and lang files through the artisan commands `php artisan config:publish michele-angioni/message-board` and `php artisan lang:publish michele-angioni/message-board`.
You can than edit the config.php file in your `app/config/packages/michele-angioni/message-board`.

In particular it can be useful to define a named route for your User page and write it the in the conf file.

## Usage

The `MicheleAngioni\MessageBoard\AbstractMbGateway` class is a prototype of the Message Board Gateway which can be used to access all main features of the message board.

A simple concrete class is provided as well and can be used. To use it, just create a new instance of `MicheleAngioni\MessageBoard\MbGateway` through by using Laravel dependency injection. 

### Managing a User Message Board

The AbstractMbGateway method `getOrderedUserPosts(MbUserInterface $user, $messageType = 'all', $page = 1, $limit = 20, , $applyPresenter = false, $escapeText = false, , MbUserInterface $userVisiting = NULL)` returns a Collection of posts, ordered by datetime, posted in the $user message board.$user is an instance of the User model (which must implement the MbUserInterface) 
$messageType defines the type of the messages you want to retrieve, 'all' will retrieve all posts in the User message board.
$page and $limit handle pagination.
$applyPresenter states if posts and comments must be passed to the presenter before being returned
$escateText states if post and comment texts must be escaped before being returned
$userVisiting is the instance of the User model (which must implement the MbUserInterface) of the user who is requesting the posts. Leave it null if $user is requesting its own posts 

A particularly useful feature is the "user last view datetime", that is when a user sees his own message board the datetime of the visit can be saved to remember which posts have been already seen and which not.
To achieve that, just call the `updateUserLastView(MbUserInterface $user)` method, where $user is an instance of your User model.
You can then retrieve the saved datetime by calling the `getLastViewDatetime()` method from your user model. You can than use it in your classes or views.

By setting $applyPresenter to true, the posts will also be passed to a `PostPresenter` before being returned. 
By setting $escapeText to true, the Post and Comment text will be escaped by [HtmlPurifier](https://github.com/mewebstudio/Purifier) so that can be securely echoed in your views.
In the config file you find the default rules used by Message Board under the `mb_purifier_conf` key. You can easily customize it by looking at the [HtmlPurifier](https://github.com/mewebstudio/Purifier) documentation.
If you want to use your own text purifier, create your own class which must implement the `MicheleAngioni\MessageBoard\PurifierInterface` interface. You then have to override the binding in the `MessageBoardServiceProvider`, that is define is a custom service provider

    $this->app->bind(
            'MicheleAngioni\MessageBoard\PurifierInterface',
            'Namespace\YourOwnPurifier'
        );

You can also manually pass a single model to the presenter by using the `presentModel(MbUserInterface $user, $model, $escapeText = false)` method, or even an entire collection through `presentCollection(MbUserInterface $user, Collection $collection, $escapeText = false)`.

### Managing posts

Use the `createPost(MbUserInterface $user, $idPoster = NULL, $messageType = 'public_mess', $text)` method to create a new post.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$isPoster is the primary id of the User who writes the post, $messageType defines the type of the message will be posted and $text is the test of the post.
Messages of type 'private_mess' are marked as unread by default. Other messages are datetime based (see below).

Use the `getPost($idPost)` and `deletePost($idPost)` methods to respectively retrieve and delete a post.

### Managing comments

Use the `createComment(MbUserInterface $user, $postId, $text)` method to create a new comment.
$user is an instance of your User model (which must implement the MbUserInterface) which will own the comment.
$postId is the post where the comment belongs.
$text is the text of the comment.

Use the `getComment($idComment)` and `deleteComment($idComment)` methods to respectively get and delete a comment.

### Managing likes

Use the `createLike($idUser, $likableEntityId, $likableEntity)` to add a like.
$isUser is the User who gives the like. $likableEntity is the entity which is liked: 'post' and 'comment' are supported by default.
$likableEntityId is the primary id of the entity which is liked.

The `deleteLike($idUser, $likableEntityId, $likableEntity)` works in the same way, but instead it deletes the like.

### (optional) Coded posts

Message Board supports also coded posts, that is in the messageboard.php lang file you can define codes with pre-defined messages.

You can use the `createCodedPost(MbUserInterface $user, $messageType = 'public_mess', $code, array $attributes = array())` method to access the coded messages.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$messageType defines the type of the message will be posted.
$code is the key of the lang file array which identifies the coded message.
$attributes defines a list of variables can be injected in the coded message. See the [Laravel localization documentation](http://laravel.com/docs/4.2/localization) for further details.

If you want a deeper level of customization for your coded posts, you can extend the `AbstractMbGateway` and create your own `getCodedPostText($code, MbUserInterface $user, array $attributes)` method which must return the text of the coded message.

## Contribution guidelines

Pull requests are welcome, especially for the to do list below.

## To do list

- images handling
- emoticons management

## License

Message Board is distributed under the terms of the Apache 2.0 license.

## Contacts

Fell free to contact me.
