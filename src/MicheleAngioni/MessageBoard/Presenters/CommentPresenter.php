<?php

namespace MicheleAngioni\MessageBoard\Presenters;

use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Contracts\PurifierInterface;
use MicheleAngioni\MessageBoard\Models\Like;
use MicheleAngioni\Support\Presenters\AbstractPresenter;
use MicheleAngioni\Support\Presenters\PresentableInterface;

class CommentPresenter extends AbstractPresenter implements PresentableInterface
{
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

    /**
     * Return Comment text, property escaped if requested
     *
     * @return string
     */
    public function text()
    {
        if($this->escapeText) {
            return $this->purifier->clean($this->object->text, 'ma_messageboard.mb_purifier_conf');
        }

        return $this->object->text;
    }

    /**
     * Return Comment primary key
     *
     * @return int
     */
    public function getKey()
    {
        return $this->object->getKey();
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
     * Return Comment creation datetime
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->object->getCreatedAt();
    }

    /**
     * Check if the Comment author wrote on a Post owned by him/herself
     *
     * @return bool
     */
    public function isInAuthorPost()
    {
        return $this->object->isInAuthorPost();
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
            $this->object->likes->each(function(Like $like) {
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
