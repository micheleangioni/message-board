<?php namespace MicheleAngioni\MessageBoard;

interface PurifierInterface {

    function clean($dirtyText, $config = 'default');

}
