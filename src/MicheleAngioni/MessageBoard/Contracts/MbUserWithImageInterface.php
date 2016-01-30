<?php namespace MicheleAngioni\MessageBoard\Contracts;

/**
 * The MbUserInterface is used in the Notification creation
 *
 * @package MicheleAngioni\MessageBoard
 */
interface MbUserWithImageInterface {

    /**
     * Return the filename of the image associated with the Model, included the extension.
     *
     * @return string
     */
    public function getProfileImageFilename();

}
