<?php

namespace MicheleAngioni\MessageBoard\Repos;

use Helpers;
use Illuminate\Database\Eloquent\Builder;
use MicheleAngioni\Support\Repos\AbstractEloquentRepository;
use MicheleAngioni\MessageBoard\Contracts\PostRepositoryInterface;
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
     * {@inheritdoc}
     */
    public function deleteOldMessages($datetime, $category = null)
    {
        if(!Helpers::checkDatetime($datetime)) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $datetime is not a valid datetime.');
        }

        $query = $this->model;

        if($category) {
            $query = $query->where('category_id', '=', $category);
        }

        $posts = $query->where('created_at', '<', $datetime)->get();

        foreach($posts as $post) {
            $post->delete();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderedPosts($idUser, $category = false, $private = null, $page = 1, $limit = 20)
    {
        if(!(Helpers::isInt($idUser,1) && Helpers::isInt($page,1) && Helpers::isInt($limit,1))) {
            throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $idUser, $page or $limit are not valid positive integers.');
        }

        // Take all posts in the mb, ordered by Post AND Comment datetime.

        try {
            if($category === false) {
                // No Category constraints, check private requirements

                if(is_null($private)) {
                    // Retrieve all posts regardless of categories or privacy

                    $posts = $this->getBy(
                        ['user_id' => $idUser],
                        ['likes', 'poster', 'comments.likes.user', 'comments.user']
                    );
                }
                elseif($private === false) {
                    // Retrieve all Posts regardless of Categories, but if a Post has a Category, take it only if public

                    $posts = $this->model
                        ->with(['likes', 'poster', 'comments.likes.user', 'comments.user'])
                        ->where('user_id', $idUser)
                        ->where(function(Builder $query)
                        {
                            $query->whereNull('category_id')
                                ->orWhere(function ($query) {
                                    $query->whereHas('category', function(Builder $q)
                                    {
                                        $q->where('private', false);
                                    });
                                });
                        })
                        ->get();
                }
                elseif($private === true) {
                    // Retrieve only posts with a private Category

                    $posts = $this->model
                        ->with(['likes', 'poster', 'comments.likes.user', 'comments.user'])
                        ->where('user_id', $idUser)
                        ->where(function(Builder $query)
                        {
                            $query->whereHas('category', function(Builder $q)
                            {
                                $q->where('private', true);
                            });
                        })
                        ->get();
                }
                else {
                    throw new InvalidArgumentException('InvalidArgumentException in '.__METHOD__.' at line '.__LINE__.': $private must be null or boolean.');
                }
            }
            elseif(is_null($category)) {
                // Only Posts with no Category relationships

                $posts = $this->getBy(
                    ['user_id' => $idUser, 'category_id' => null],
                    ['likes', 'poster', 'comments.likes.user', 'comments.user']
                );
            }
            elseif(Helpers::isInt($category,1)) {
                if(is_null($private)) {
                    $posts = $this->getBy(
                        ['user_id' => $idUser, 'category_id' => $category],
                        ['likes', 'poster', 'comments.likes.user', 'comments.user']
                    );
                }
                else {
                    $posts = $this->whereHas(
                        'category',
                        ['user_id' => $idUser],
                        ['private' => $private],
                        ['likes', 'poster', 'comments.likes.user', 'comments.user']
                    );
                }
            }
            else{
                if(is_null($private)) {
                    $posts = $this->whereHas(
                        'category',
                        ['user_id' => $idUser],
                        ['name' => $category],
                        ['likes', 'poster', 'comments.likes.user', 'comments.user']
                    );
                }
                else {
                    $posts = $this->whereHas(
                        'category',
                        ['user_id' => $idUser],
                        ['name' => $category, 'private' => $private],
                        ['likes', 'poster', 'comments.likes.user', 'comments.user']
                    );
                }
            }
        } catch (Exception $e) {
            throw new DatabaseException("DB error in ".__METHOD__.' at line '.__LINE__.':'. $e->getMessage());
        }

        $posts = $posts->sortBy('child_datetime')->reverse();

        // Handle pagination

        $offset = $limit * ($page - 1);
        $pagedPosts = $posts->slice($offset, $limit)->values();

        return $pagedPosts;
    }
}
