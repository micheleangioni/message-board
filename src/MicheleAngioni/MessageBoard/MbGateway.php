<?php namespace MicheleAngioni\MessageBoard;

use MicheleAngioni\MessageBoard\Contracts\MbGatewayInterface;
use MicheleAngioni\MessageBoard\Services\CategoryService;
use MicheleAngioni\MessageBoard\Services\MessageBoardService;

class MbGateway extends AbstractMbGateway implements MbGatewayInterface {

    function __construct(CategoryService $categoryService, MessageBoardService $messageBoardService)
    {
        parent::__construct($categoryService, $messageBoardService);
    }

}
