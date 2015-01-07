<?php namespace MicheleAngioni\MessageBoard;

/**
 * The MbUserInterface is used by other MessageBoard components to require a User model.
 *
 * @package MicheleAngioni\MessageBoard
 */
interface MbUserInterface {

    public function getPrimaryId();

    public function getUsername();

}