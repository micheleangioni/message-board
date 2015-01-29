<?php namespace MicheleAngioni\MessageBoard;

use MicheleAngioni\MessageBoard\PurifierInterface;
use MicheleAngioni\MessageBoard\Repos\CommentRepositoryInterface as CommentRepo;
use MicheleAngioni\MessageBoard\Repos\LikeRepositoryInterface as LikeRepo;
use MicheleAngioni\MessageBoard\Repos\PostRepositoryInterface as PostRepo;
use MicheleAngioni\MessageBoard\Repos\RoleRepositoryInterface as RoleRepo;
use MicheleAngioni\MessageBoard\Repos\ViewRepositoryInterface as ViewRepo;
use MicheleAngioni\Support\Presenters\Presenter;
use Lang;
use InvalidArgumentException;

class MbGateway extends AbstractMbGateway implements MbGatewayInterface {

    protected $langFile = 'messageboard';

    protected $mbText;

    function __construct(CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, Presenter $presenter,
                         PurifierInterface $purifier, RoleRepo $roleRepo, ViewRepo $viewRepo)
    {
        parent::__construct($commentRepo, $likeRepo, $postRepo, $presenter, $purifier, $roleRepo, $viewRepo);
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
