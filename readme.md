# MESSAGE BOARD

[![License](https://poser.pugx.org/michele-angioni/message-board/license.svg)](https://packagist.org/packages/michele-angioni/message-board)
[![Latest Stable Version](https://poser.pugx.org/michele-angioni/message-board/v/stable)](https://packagist.org/packages/michele-angioni/message-board)
[![Build Status](https://travis-ci.org/micheleangioni/message-board.svg)](https://travis-ci.org/micheleangioni/message-board)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5/small.png)](https://insight.sensiolabs.com/projects/e277b232-bef0-4576-bc1a-83b2d1d6a1f5)

Message Board is a [Laravel 5](http://laravel.com) package which assigns a message board to each User, where posts and comments can be posted.

Bans and a permission system are provided out of the box. Social features such as "likes" are included as well.

The package comes bundled with a highly customizable full featured API, which needs to be enabled in the config file, to let your application use Message Board through asynchronous calls without having to write your own API.    

## Documentation

Check our [wiki](https://github.com/micheleangioni/message-board/wiki) for full documentation.

## Quick examples

**Retrieve a User Posts**

	MessageBoard::getOrderedUserPosts($user);

**Create a new Post**
	
	public function store(Request $request)
	{
		[...]
		
		$user = User::findOrFail($request->get('id_user'));
		
		MessageBoard::createPost($user, Auth::user(), null, $text);

		[...]				
	}

**Create a Like**

	public function postLikeStore($idPost, Request $request)
	{
		[...]
		
		MessageBoard::createLike(Auth::user()->getKey(), $idPost, 'post');

		[...]				
	}

**Ban a User**

	public function banUser($idUser, Request $request)
	{
		[...]
		
		MessageBoard::banUser(Auth::user(), $request->get('days'), $request->get('reason'));
		
		[...]
	}

**Read all User Notifications**

	public function readNotifications()
	{
		$user = Auth::user();
		
		$user->readAllNotifications();				
	}

## Contribution guidelines

Support follows PSR-1 and PSR-4 PHP coding standards, and semantic versioning.

Please report any issue you find in the issues page.  

Use development branch for pull requests.

## License

Message Board is free software distributed under the terms of the MIT license.
