<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface PurifierInterface
{
    function clean($dirtyText, $config = 'default');
}
