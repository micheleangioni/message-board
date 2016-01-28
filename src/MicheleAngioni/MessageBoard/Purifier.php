<?php namespace MicheleAngioni\MessageBoard;

use Mews\Purifier\Purifier as HtmlPurifier;
use MicheleAngioni\MessageBoard\Contracts\PurifierInterface;

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
     * @param  string  $configuration
     *
     * @return string
     */
    public function clean($dirtyText, $configuration = 'default')
    {
        return $this->purifier->clean($dirtyText, $configuration);
    }

}
