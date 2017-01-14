<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface MbGatewayInterface
{
    public function createCodedPost(MbUserInterface $user, $categoryId, $code, array $attributes);
}
