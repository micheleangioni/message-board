<?php namespace MicheleAngioni\MessageBoard;

use Chromabits\Purifier\Contracts\Purifier as HtmlPurifier;

class Purifier implements PurifierInterface {

    /**
     * @var HtmlPurifier
     */
    protected $purifier;

    function __construct(HtmlPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * Clean the input text and return it clean
     *
     * @param  string  $dirtyText
     * @param  array  $configuration
     *
     * @return string
     */
    public function clean($dirtyText, array $configuration)
    {
        return $this->purifier->clean($dirtyText, $configuration);
    }

}
