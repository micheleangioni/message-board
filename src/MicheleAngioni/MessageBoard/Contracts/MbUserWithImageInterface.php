<?php

namespace MicheleAngioni\MessageBoard\Contracts;

interface MbUserWithImageInterface
{
    /**
     * Return the filename of the image associated with the Model, included the extension.
     *
     * @return string
     */
    public function getProfileImageFilename();
}
