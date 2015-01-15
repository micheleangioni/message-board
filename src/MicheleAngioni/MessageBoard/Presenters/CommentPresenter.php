<?php namespace MicheleAngioni\MessageBoard\Presenters;

use MicheleAngioni\MessageBoard\MbUserInterface;
use MicheleAngioni\Support\Presenters\AbstractPresenter;
use MicheleAngioni\Support\Presenters\PresentableInterface;

class CommentPresenter extends AbstractPresenter implements PresentableInterface {

    /**
     * Laravel application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

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


	function __construct(MbUserInterface $user, $escapeText = false, $app = NULL)
	{
        $this->app = $app ?: app();

        $this->escapeText = $escapeText;

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
            if(!$this->purifier) {
                $this->purifier = $this->app->make('Mews\Purifier\Purifier');
            }

            return $this->purifier->clean($this->object->text, $this->app['config']->get('message-board::mb_purifier_conf'));
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

            $this->object->likes->each(function($like)
            {
                if($like->user_id == $this->user->getPrimaryId()) {
                    return $this->isLiked = true;
                }
            });
        }

        return $this->isLiked = false;
    }

}
