<?php namespace MicheleAngioni\MessageBoard\Transformers;

use League\Fractal\TransformerAbstract;
use MicheleAngioni\MessageBoard\Presenters\CommentPresenter;

class CommentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'likes'
    ];

    /**
     * Turn input Player model into a generic array
     *
     * @param  CommentPresenter  $comment
     * @return array
     */
    public function transform(CommentPresenter $comment)
    {
        return [
            'id' => (int)$comment->getKey(),
            'text' => $comment->getText(),
            'isInAuthorPost' => (bool)$comment->isInAuthorPost(),
            'isLiked' => (bool)$comment->isLiked(),
            'createdAt' => (string)$comment->getCreatedAt()
        ];
    }

    /**
     * Embed Likes
     *
     * @param  CommentPresenter  $comment
     * @return \League\Fractal\Resource\Collection
     */
    public function includeLikes(CommentPresenter $comment)
    {
        return $this->collection($comment->likes, new LikeTransformer);
    }

}
