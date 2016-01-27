<?php namespace MicheleAngioni\MessageBoard;

use MicheleAngioni\MessageBoard\Contracts\CategoryRepositoryInterface as CategoryRepo;
use MicheleAngioni\MessageBoard\Contracts\CommentRepositoryInterface as CommentRepo;
use MicheleAngioni\MessageBoard\Contracts\LikeRepositoryInterface as LikeRepo;
use MicheleAngioni\MessageBoard\Contracts\MbGatewayInterface;
use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Contracts\PostRepositoryInterface as PostRepo;
use MicheleAngioni\MessageBoard\Contracts\PurifierInterface;
use MicheleAngioni\MessageBoard\Contracts\ViewRepositoryInterface as ViewRepo;
use MicheleAngioni\Support\Presenters\Presenter;
use Lang;
use InvalidArgumentException;

class MbGateway extends AbstractMbGateway implements MbGatewayInterface {

    protected $langFile = 'messageboard';

    protected $mbText;

    function __construct(CategoryRepo $categoryRepo, CommentRepo $commentRepo, LikeRepo $likeRepo, PostRepo $postRepo, Presenter $presenter,
                         PurifierInterface $purifier, ViewRepo $viewRepo)
    {
        parent::__construct($categoryRepo, $commentRepo, $likeRepo, $postRepo, $presenter, $purifier, $viewRepo);
    }

    /**
     * {inheritdoc}
     * @throws InvalidArgumentException
     */
    protected function getCodedPostText($code, MbUserInterface $user = NULL, array $attributes = [])
    {
        if(!$code) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': No input code.');
        }

        // Set user attribute, is set

        if($user) {
            $variables['user'] =  $this->getUserLink($user);
        }

        return $this->mbText = Lang::get('ma_messageboard::messageboard.'."$code", $variables);
    }

}
