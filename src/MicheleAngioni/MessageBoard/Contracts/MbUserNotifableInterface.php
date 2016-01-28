<?php namespace MicheleAngioni\MessageBoard\Contracts;

/**
 * The MbUserInterface is used in the Notification Creation
 *
 * @package MicheleAngioni\MessageBoard
 */
interface MbUserNotifableInterface {

    /**
     * Return the filename of the image associated with the Model, included the extension.
     *
     * @return string
     */
    public function getProfileImageFilename();

}
