<?php namespace MicheleAngioni\MessageBoard\Facades;

use Illuminate\Support\Facades\Facade;

class MessageBoard extends Facade {

	protected static function getFacadeAccessor() { return 'messageboard'; }

}
