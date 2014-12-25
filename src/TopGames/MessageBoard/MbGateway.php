<?php

namespace TopGames\MessageBoard\TopPlayer;

use TopGames\MessageBoard\AbstractMbGateway;
use TopGames\MessageBoard\MbUserInterface;
use TopGames\MessageBoard\MbGatewayInterface;
use TopGames\MessageBoard\Repos\CommentRepositoryInterface as CommentRepo;
use TopGames\MessageBoard\Repos\LikeRepositoryInterface as LikeRepo;
use TopGames\MessageBoard\Repos\PostRepositoryInterface as PostRepo;
use TopGames\MessageBoard\Repos\ViewRepositoryInterface as ViewRepo;
use Lang;
use InvalidArgumentException;

class MbGateway extends AbstractMbGateway implements MbGatewayInterface {

    protected $mbText;

    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, ViewRepo $viewRepo)
    {
        parent::__construct($commentRepo, $likeRepo, $postRepo, $viewRepo);
    }

    /**
     * Create the text of the mb post will be sent.
     * Keys in the mb_topplayer lang file will be used for localization.
     *
     * @param  string           $code
     * @param  MbUserInterface  $user
     * @param  array            $attributes
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function setCodedPostText($code, MbUserInterface $user, array $attributes)
    {
        if(!$code) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': No input code.');
        }

        // Set user attribute

        $variables['user'] =  $this->getUserLink($user);

        return $this->mbText = Lang::get("$code", $variables);
    }

}