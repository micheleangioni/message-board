<?php namespace MicheleAngioni\MessageBoard;

interface PurifierInterface {

    function clean($dirtyText, array $configuration);

}
