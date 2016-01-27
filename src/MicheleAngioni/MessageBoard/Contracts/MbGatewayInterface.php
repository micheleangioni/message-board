<?php namespace MicheleAngioni\MessageBoard\Contracts;

use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;

interface MbGatewayInterface {

    function createCodedPost(MbUserInterface $user, $categoryId, $code, array $attributes);

}
