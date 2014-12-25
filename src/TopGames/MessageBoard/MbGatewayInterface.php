<?php

namespace TopGames\MessageBoard;

interface MbGatewayInterface {

    function createCodedPost(MbUserInterface $user, $messageType, $code, array $attributes);

}