<?php namespace MicheleAngioni\MessageBoard\Presenters;

use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Contracts\PurifierInterface;
use MicheleAngioni\MessageBoard\Models\Like;
use MicheleAngioni\Support\Presenters\AbstractPresenter;
use MicheleAngioni\Support\Presenters\PresentableInterface;

class PostPresenter extends AbstractPresenter implements PresentableInterface {

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


	function __construct(MbUserInterface $user, $escapeText = false, PurifierInterface $purifier)
	{
        $this->escapeText = $escapeText;

        $this->purifier = $purifier;

		$this->user = $user;

        $this->lastView = $user->mbLastView ? $user->mbLastView->datetime : NULL;
	}
	
	
	public function post_type()
	{
        return $this->object->post_type;
	}

    public function user_id()
    {
        return $this->object->user_id;
    }

    public function poster_id()
    {
        return $this->object->poster_id;
    }

    public function text()
    {
        if($this->escapeText) {
            return $this->purifier->clean($this->object->text, config('ma_messageboard.mb_purifier_conf'));
        }

        return $this->object->text;
    }

    public function created_at()
    {
        return $this->object->created_at;
    }

    public function is_read()
    {
        return $this->object->is_read;
    }

    /**
     * Return Post text, property escaped if requested
     *
     * @return string
     */
    public function getText()
    {
        return $this->text();
    }

    /**
     * Return false if the Post has been read, false otherwise
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->object->isRead();
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

    /**
     * Check if the post is new to input user
     *
     * @return bool
     */
    public function isNew()
    {
        // Check if the user is seeing a post of another mb
        if($this->object->user_id != $this->user->getPrimaryId()) {
            return false;
        }

        // Check if a isNew value is already stored
        if(!is_null($this->isNew)) {
            return $this->isNew;
        }

        // Confront the user lastView with the post child_datetime
        if(is_null($this->lastView)) {
            return $this->isNew = true;
        }
        elseif($this->object->child_datetime > $this->user->mbLastView->datetime) {
            return $this->isNew = true;
        }
        else {
            return $this->isNew = false;
        }
    }

}
