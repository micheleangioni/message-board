<?php

namespace TopGames\MessageBoard;

/**
 * The MbUserInterface is used by other MessageBoard components to require a User model.
 *
 * @package TopGames\MessageBoard
 */
interface MbUserInterface {

    public function getPrimaryId();

    public function getUsername();

}