# MESSAGE BOARD

## Introduction

The Message Board is a [Laravel 4](http://laravel.com) package which assigns a message board to each User, where posts and comments can be posted. Social features like "likes" are included as well.

## Installation

Message Board can be installed through Composer, just include `"michele-angioni/message-board": "dev-master"` to your composer.json.

## Configuration

The Message Board Service Provided must be added in the app.php config file, under the providers array

    'MicheleAngioni\MessageBoard\MessageBoardServiceProvider'

Add the `MbTrait` to your User model. It has also to implement the `MbUserInterface` so that Message Board classes can type hint it

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

In particular it can be useful to define a named route for your User page and copy it the in the conf file.

## Usage

The `MicheleAngioni\MessageBoard\AbstractMbGateway` class is a prototype of the Message Board Gateway which can be used to access all main features of the message board.

A simple concrete class is provided as well and can be directly used to try the Message Board features:

### Managing a User Message Board

The method `getOrderedUserPosts(MbUserInterface $user, $messageType = 'all', $page = 1, $limit = false)` return a Collection of posts, ordered by datetime.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$messageType defines the type of the messages you want to retrieve, 'all' will retrieve all posts in the User message board.
$page and $limit handle pagination.

A particularly useful feature is the "user last view datetime", that is when a user sees his own message board the datetime of the visit can be saved to remember which posts have been already seen and which not.
To achieve that call the `updateUserLastView(MbUserInterface $user)` method, where $user is an instance of your User model.
You can then retrieve the saved datetime by calling the `getLastViewDatetime()` method from your user model. You can than use it in your classes or views.

### Managing posts

Use the `createPost(MbUserInterface $user, $idPoster = NULL, $messageType = 'public_mess', $text)` method to create a new post.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$isPoster is the primary id of the User who writes the post, $messageType defines the type of the message will be posted and $text is the test of the post.
Messages of type 'private_mess' are marked as unread by default. Other messages are datetime based (see below).

Use the `getPost($idPost)` and `deletePost($idPost)` methods to respectively get and delete an input post.

### Managing comments

Use the `createComment(MbUserInterface $user, $postId, $text)` method to create a new post.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$postId is the post where the comment belongs.
$text is the text of the comment.

Use the `getComment($idComment)` and `deleteComment($idComment)` methods to respectively get and delete an input comment.

### Managing likes

Use the `createLike($idUser, $likableEntityId, $likableEntity)` to add a like.
$isUser is the User who gives the like. $likableEntity is the entity which is liked: 'post' and 'comment' are supported by default.
$likableEntityId is the primary id of the entity which is liked.

The `deleteLike($idUser, $likableEntityId, $likableEntity)` works in the same way, but instead it deletes the like.

### (optional) Coded posts

By extending the `AbstractMbGateway` coded messages can be handled. In the messageboard.php lang file you can define codes with pre-defined messages.

You can than use the `createCodedPost(MbUserInterface $user, $messageType = 'public_mess', $code, array $attributes = array())` method to access the coded messages.
$user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.
$messageType defines the type of the message will be posted.
$code is the key of the lang file array which identifies the coded message.
$attributes defines a list of variables can be injected in the coded message. See the [Laravel localization documentation](http://laravel.com/docs/4.2/localization) for further details.

If you want a deeper level of customization for your coded posts, you can extend the AbstractMbGateway and create your own `getCodedPostText($code, MbUserInterface $user, array $attributes)` method which must return the test of the coded message.

## Contribution guidelines

Pull requests are welcome, especially for the to do list below.

## To do list

- images handling
- emoticons management
- default presenters

## Contacts

Fell free to contact me.