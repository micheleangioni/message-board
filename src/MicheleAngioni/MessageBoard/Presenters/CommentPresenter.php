<?php namespace MicheleAngioni\MessageBoard\Presenters;

use MicheleAngioni\MessageBoard\PurifierInterface;
use MicheleAngioni\MessageBoard\MbUserInterface;
use MicheleAngioni\Support\Presenters\AbstractPresenter;
use MicheleAngioni\Support\Presenters\PresentableInterface;
use Config;

class CommentPresenter extends AbstractPresenter implements PresentableInterface {

    /**
     * @var bool
     */
    protected $escapeText;

    /**
     * @var bool
     */
    protected $isLiked;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @var string
     */
    protected $lastView;

    protected $purifier;

    /**
     * User visiting the message board where the post belongs
     *
     * @var MbUserInterface
     */
    protected $user;

    /**
     * @param  MbUserInterface  $user
     * @param  bool  $escapeText
     * @param  PurifierInterface  $purifier
     */
	function __construct(MbUserInterface $user, $escapeText = false, PurifierInterface $purifier)
	{
        $this->escapeText = $escapeText;

        $this->purifier = $purifier;

		$this->user = $user;
	}


    public function post_id()
    {
        return $this->object->post_id;
    }

    public function user_id()
    {
        return $this->object->user_id;
    }

    public function text()
    {
        if($this->escapeText) {
            return $this->purifier->clean($this->object->text, Config::get('ma_messageboard.mb_purifier_conf'));
        }

        return $this->object->text;
    }

    public function created_at()
    {
        return $this->object->created_at;
    }


    /**
     * Return Comment text, property escaped if requested
     *
     * @return string
     */
    public function getText()
    {
        return $this->text();
    }

    /**
     * Return if the post is liked by the input user
     *
     * @return bool
     */
    public function isLiked()
    {
        if(!is_null($this->isLiked)) {
            return $this->isLiked;
        }

        if(!$this->object->likes->isEmpty()) {
            $this->object->likes->each(function($like) {
                if($like->user_id == $this->user->getPrimaryId()) {
                    return $this->isLiked = true;
                }
            });
        }

        if($this->isLiked) {
            return true;
        }

        return $this->isLiked = false;
    }

}
