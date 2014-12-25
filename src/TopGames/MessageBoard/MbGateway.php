<?php

namespace TopGames\MessageBoard;

use TopGames\MessageBoard\Repos\CommentRepositoryInterface as CommentRepo;
use TopGames\MessageBoard\Repos\LikeRepositoryInterface as LikeRepo;
use TopGames\MessageBoard\Repos\PostRepositoryInterface as PostRepo;
use TopGames\MessageBoard\Repos\ViewRepositoryInterface as ViewRepo;
use Lang;
use InvalidArgumentException;

class MbGateway extends AbstractMbGateway implements MbGatewayInterface {

    protected $langFile = 'messageboard';

    protected $mbText;

    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, ViewRepo $viewRepo)
    {
        parent::__construct($commentRepo, $likeRepo, $postRepo, $viewRepo);
    }

    /**
     * Create the coded text of the mb post.
     * Keys in the messageboard.php lang file will be used for localization.
     *
     * @param  string           $code
     * @param  MbUserInterface  $user
     * @param  array            $attributes
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getCodedPostText($code, MbUserInterface $user = NULL, array $attributes)
    {
        if(!$code) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': No input code.');
        }

        // Set user attribute, is set

        if($user) {
            $variables['user'] =  $this->getUserLink($user);
        }

        return $this->mbText = Lang::get('message-board::messageboard.'."$code", $variables);
    }

}