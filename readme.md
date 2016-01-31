# MESSAGE BOARD

[![License](https://poser.pugx.org/michele-angioni/message-board/license.svg)](https://packagist.org/packages/michele-angioni/message-board)
[![Latest Stable Version](https://poser.pugx.org/michele-angioni/message-board/v/stable)](https://packagist.org/packages/michele-angioni/message-board)
[![Build Status](https://travis-ci.org/micheleangioni/message-board.svg)](https://travis-ci.org/micheleangioni/message-board)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5/small.png)](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5)

## Introduction

Message Board is a [Laravel 5](http://laravel.com) package which assigns a message board to each User, where posts and comments can be posted.  
Bans and a roles and permission system is provided out of the box. Social features such as "likes" are included as well.

## Installation

Message Board can be installed through Composer, first of all include 

    "michele-angioni/message-board": "~1.0"
    
into your composer.json and run `composer update` or `composer install`.  
Then publish the Message Board conf and lang files through the artisan command `php artisan vendor:publish`. It will create the `ma_messageboard.php` file in your config directory.  
Add the Message Board Service Provider in the `app.php` config file, under the providers array

    'MicheleAngioni\MessageBoard\MessageBoardServiceProvider'

and the MessageBoard facade in the aliases array

    'MessageBoard' => 'MicheleAngioni\MessageBoard\Facades\MessageBoard'

You can now run migrations and seeding through `php artisan migrate --path="database/migrations/messageboard"` and `php artisan db:seed --class="MessageBoardSeeder"` and you are done.

**N.B** : Message Board needs [MicheleAngioni/Support](https://github.com/micheleangioni/support)'s Helpers facade to work, so be sure to register it in the app.php file under the aliases array as
                                                                                               
    'Helpers' => 'MicheleAngioni\Support\Facades\Helpers'

If you are looking for the Laravel 4 version, check the not-anymore-maintained [0.1 version](https://github.com/micheleangioni/message-board/tree/6cf137951184597bec2eafa37134b7093d61fd64) and its documentation.

## Configuration

You can than edit the `ma_messageboard.php` file in your `app/config` directory.
You need to set the correct Model you want to associate a Message Board. Usually it is the Auth model used by Laravel.

Additionally, it can be useful to define a named route for your User page. An id parameters will be used as well calling `/named_route/{id}` url.

## Usage

First of all add the `MbTrait` to your User model, which has also to implement the `MbUserInterface` 

    <?php

    use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
    use MicheleAngioni\MessageBoard\MbTrait;

    class User extends \Illuminate\Database\Eloquent\Model implements MbUserInterface {

        use MbTrait;

        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'users';

        [...]
        
        public function getPrimaryId()
        {
            //
        }
        
        public function getUsername()
        {
            //
        }
        
        [...]

The `MessageBoard` Facade is provided with all methods you need to efficiently use the Message Board.
  
### Retrieving Posts

    MessageBoard::getOrderedUserPosts(MbUserInterface $user, $category = false, $private = null, $page = 1, $limit = 20, $applyPresenter = false, $escapeText = false, MbUserInterface $userVisiting = null)
    
returns a Collection of posts, ordered by datetime, posted in the $user message board, where $user is an instance of the User model (which must implement the MbUserInterface):     

 - $category defines the Category to which the Post belongs. Posts can belong to any Category;
 - $private defines is retrieved Posts must be private (=true), public (=false) or doesn't matter (=null);
 - $page and $limit handle pagination;
 - $applyPresenter states if Posts and Comments must be passed to the presenter before being returned;  
 - $escateText states if post and comment texts must be escaped before being returned;
 - $userVisiting is the instance of the User model (which must implement the MbUserInterface) of the user who is requesting the posts. Leave it null if $user is requesting its own posts.  

By setting $applyPresenter to true, the posts will also be passed to a `PostPresenter` before being returned.   
By setting $escapeText to true, the Post and Comment text will be escaped by [HtmlPurifier](https://github.com/mewebstudio/Purifier) so that can be securely echoed in your views.  
In the config file you find the default rules used by Message Board under the `mb_purifier_conf` key. You can easily customize it by looking at the [HtmlPurifier](https://github.com/mewebstudio/Purifier) documentation.  
If you want to use your own text purifier, create your own class which must implement the `MicheleAngioni\MessageBoard\PurifierInterface` interface. You then have to override the binding in the `MessageBoardServiceProvider`, that is define is a custom service provider

    $this->app->bind(
            'MicheleAngioni\MessageBoard\Contracts\PurifierInterface',
            'Namespace\YourOwnPurifier'
        );

You can also manually pass a single model to the presenter by using the `presentModel(MbUserInterface $user, $model, $escapeText = false)` method, or even an entire collection through `presentCollection(MbUserInterface $user, Collection $collection, $escapeText = false)`.

### Managing posts

To create a new Post use the createPost() method

    MessageBoard::createPost(MbUserInterface $user, MbUserInterface $poster = null, $categoryId = null, $text, $banCheck = true) 
    
 - $user and $poster are instances of your User model (which must implement the MbUserInterface) of the owner of the message board where the post will be posted and the poster. If a User writes a post on his/her own messageboard, use the same User instance of both first and second parameters; 
 - $categoryId defines the Category of the Post;
 - $text is the text of the message;
 - $banCheck states if a ban check on the poster user will be performed.  

Use the `getPost($idPost)`, `updatePost($idPost, $text, MbUserInterface $user = null)` and the `deletePost($idPost, MbUserInterface $user = NULL)` methods to respectively retrieve, update and delete a Post.  

In the `updatePost` and `deletePost` methods, you can specify a User as second parameter. The system will check if the user has the rights (i.e. owns the Post or he/she has proper permissions (see below)) to delete it. If not it will rise a PermissionsException.

### (optional)

Posts can behave to Categories. You can manage Categories through the following methods

    MessageBoard::

### Managing comments

The `createComment(`) method can be used to create a new Comment 

    MessageBoard::createComment(MbUserInterface $user, $postId, $text, $banCheck = true)

 - $user is an instance of your User model (which must implement the MbUserInterface) which will own the comment;
 - $postId is the post where the comment belongs;
 - $text is the text of the comment;
 - $banCheck states if a ban check on the user will be performed.

Use the `MessageBoard::getComment($idComment)`, the `MessageBoard::updateComment($idComment, $text, MbUserInterface $user = null)` and `MessageBoard::deleteComment($idComment, MbUserInterface $user = NULL)` methods to respectively get, update and delete a Comment.
  
In the `updateComment` and `deleteComment` methods, you can specify a User as second parameter. The system will check if the user has the rights (i.e. owns the Comment or he/she has proper permissions (see below)) to delete it. If not it will rise a PermissionsException.

### Managing likes

Likes can be created by using the methods:
 
    MessageBoard::createLike($idUser, $likableEntityId, $likableEntity)

    MessageBoard::deleteLike($idUser, $likableEntityId, $likableEntity)

 - $idUser is the User who gives the like. $likableEntity is the entity which is liked: 'post' and 'comment' are supported by default;  
 - $likableEntityId is the primary id of the entity which is liked, i.e. a Post or a Comment;
 - $likableEntity is a string and can have the values `'post'` or `'comment'`.

### (optional) Coded posts

Message Board supports also coded posts, that is in the `messageboard.php` lang file you can define codes with pre-defined messages.

You can use the `createCodedPost(MbUserInterface $user, $categoryId = null, $code, array $attributes = [])` method to access the coded messages.  

 - $user is an instance of your User model (which must implement the MbUserInterface) where the post will be posted.  
 - $categoryId defines the Category of the Post;  
 - $code is the key of the lang file array which identifies the coded message.  
 - $attributes defines a list of variables can be injected in the coded message. See the [Laravel localization documentation](http://laravel.com/docs/5.0/localization) for further details.

The codes are defined in the `messageboard.php` lang file. More codes can be defined this way:

    return [
    
        'code' => "The user is :user.",
    
    ];

If you want a deeper level of customization for your coded posts, you can extend the `AbstractMbGateway` and create your own `getCodedPostText($code, MbUserInterface $user, array $attributes)` method which must return the text of the coded message.

### Roles and Permissions

Message Board comes with a role and permission system out of the box.  
You can manage them through the `MicheleAngioni\MessageBoard\PermissionManager` class.

Default roles are `Admin` and `Moderator`. The first one can edit and delete posts and comments, ban users, add and remove moderators, manage Categories.    
The Moderator has the same Permissions of the admin, but manage moderators and categories.

In order to retrieve one or all available roles, just call the `getRole($idRole)` and `getRoles()` methods of the PermissionManager. Each role returns its permissions through the `permissions` property.  
Thanks to the `MbTrait`, you can easily add a role to an user with `$user->attachMbRole($role)` and detach it by using `$user->attachMbRole($role)`.  

To test a user against a Role, call `$user->hasMbRole($name)` where $name is the name of the role.  

To retrieve one or all available permissions, call the `getPermission($idPermission)` and `getPermissions()` methods of the PermissionManager.

If you want to test an user against a Permission, use `$user->canMb($permission)` where $permission is the name of the permission.

Default provided permissions are:

 - 'Edit Posts'
 - 'Delete Posts'
 - 'Ban Users'
 - 'Add Moderators'
 - 'Remove Moderators'
 - 'Manage Permissions'
 - 'Manage Categories'

A new Role can be created with the `createRole($name, $permissions = NULL)` method  

 - $permissions can be a collection of permissions or an array of permission ids.

A new Permission can be created with `the createPermission($name)` method.

## Bans

Message Board supports also user bans (i.e Users won't be able to write new posts and comments). 
A user can be banned Through MbGateway's banUser() method 

    banUser(MbUserInterface $user, $days, $reason = '')  

 - $user is an instance of your User model (which must implement the MbUserInterface). He/she is the user who will get banned;
 - $days is the number of days the user will be banned. If the user is already banned, $days will be added to the current ban days. A negative $days will shorten the ban total length, which however can't be set negative;  
 - $reason is the reason of the ban. Can be left blank.

## Message Board Events

Several events are fired when main operations occur:

 -  MicheleAngioni\MessageBoard\Events\CommentCreate
 -  MicheleAngioni\MessageBoard\Events\CommentDelete
 -  MicheleAngioni\MessageBoard\Events\LikeCreate
 -  MicheleAngioni\MessageBoard\Events\LikeDestroy
 -  MicheleAngioni\MessageBoard\Events\PostCreate
 -  MicheleAngioni\MessageBoard\Events\PostDelete
 -  MicheleAngioni\MessageBoard\Events\UserBanned

This way creating listeners in your application is straightforward.

## Notifications

The Message Board package includes a Notification Service which creates notifications where several main events occur.

## API Docs

You can browse the Message Board [API Documentation](http://micheleangioni.github.io/message-board/master/index.html).
(outdated ATM)

## Contribution guidelines

Support follows PSR-1 and PSR-4 PHP coding standards, and semantic versioning.

Please report any issue you find in the issues page.  
Pull requests are welcome, especially for the to do list below.

## To do list

- soft delete
- "report"/"abuse" feature
- emoticons management

## License

Message Board is free software distributed under the terms of the MIT license.

## Contacts

Fell free to contact me.
