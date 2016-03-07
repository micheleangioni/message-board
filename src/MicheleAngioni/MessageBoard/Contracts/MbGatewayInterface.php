<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface MbGatewayInterface
{
    function createCodedPost(MbUserInterface $user, $categoryId, $code, array $attributes);
}
