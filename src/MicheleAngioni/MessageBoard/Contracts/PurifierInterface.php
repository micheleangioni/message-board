<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface PurifierInterface
{
    public function clean($dirtyText, $config = 'default');
}
