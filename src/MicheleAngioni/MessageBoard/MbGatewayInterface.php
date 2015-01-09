<?php namespace MicheleAngioni\MessageBoard;

interface MbGatewayInterface {

    function createCodedPost(MbUserInterface $user, $messageType, $code, array $attributes);

}
