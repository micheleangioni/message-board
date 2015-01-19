<?php namespace MicheleAngioni\MessageBoard\Repos;

use Helpers;
use Illuminate\Support\Collection;
use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Models\Post;
use MicheleAngioni\Support\Exceptions\DatabaseException;
use Exception;
use InvalidArgumentException;

class EloquentPostRepository extends AbstractEloquentRepository implements PostRepositoryInterface
{
    protected $model;


    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    /**
     * Delete mb posts of input date, older than input date. Return true on success.
     *
     * @param  string    $type
     * @param  string    $datetime
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function deleteOldMessages($type, $datetime)
    {
        if( !Helpers::checkDatetime($datetime) ) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $datetime is not a valid datetime.');
        }

        $posts = $this->model->where('post_type', '=', $type)
            ->where('created_at', '<', $datetime)
            ->get();

        foreach($posts as $post) {
            $post->delete();
        }

        return true;
    }

    /**
     * Return posts of input type of the input user, ordered by post AND comment datetime.
     * It also sets the child_datetime post attribute.
     *
     * @param  int     $idUser
     * @param  string  $messageType = 'public_mess'
     * @param  int     $page = 1
     * @param  int     $limit = 20
     * @throws InvalidArgumentException
     * @throws DatabaseException
     *
     * @return Collection
     */
    public function getOrderedPosts($idUser, $messageType = 'public_mess', $page = 1, $limit = 20)
    {
        if( !(Helpers::isInt($idUser,1) && Helpers::isInt($page,1) && Helpers::isInt($limit,1))) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $idUser, $page or $limit are not valid integers.');
        }

        // Take all posts in the mb, ordered by Post AND Comment datetime.

        try {
            if($messageType != 'all') {
                $posts = $this->getBy(array('user_id' => $idUser, 'post_type' => $messageType), array('likes', 'poster', 'comments.likes.user', 'comments.user'));
            }
            else {
                $posts = $this->getBy(array('user_id' => $idUser), array('likes', 'poster', 'comments.likes', 'comments.user'));
            }
        } catch (Exception $e) {
            throw new DatabaseException("DB error in ".__METHOD__.' at line '.__LINE__.':'. $e->getMessage());
        }

        $posts->each(function($post)
        {
            $post->child_datetime = (string)$post->created_at;

            if(!$post->comments->isEmpty())
            {
                $post->comments = $post->comments->sortBy('created_at');

                if($post->child_datetime < $lastCommentDatetime = (string)$post->comments->last()->created_at)
                {
                    $post->child_datetime = $lastCommentDatetime;
                };
            }
        });

        $posts = $posts->sortBy('child_datetime')->reverse();

        // Handle pagination

        $offset = $limit * ($page - 1);
        $pagedPosts = $posts->slice($offset, $limit);

        return $pagedPosts;
    }

}
