<?php namespace MicheleAngioni\MessageBoard\Transformers;

use League\Fractal\TransformerAbstract;
use MicheleAngioni\MessageBoard\Presenters\CommentPresenter;
use MicheleAngioni\MessageBoard\Presenters\PostPresenter;

class PostTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'comments',
        'likes'
    ];

    /**
     * Turn input Player model into a generic array
     *
     * @param  PostPresenter  $post
     * @return array
     */
    public function transform(PostPresenter $post)
    {
        return [
            'id' => (int)$post->getKey(),
            'text' => $post->getText(),
            'isRead' => (bool)$post->isRead(),
            'isInAuthorMb' => (bool)$post->isInAuthorMb(),
            'isLiked' => (bool)$post->isLiked(),
            'isNew' => (bool)$post->isNew(),
            'createdAt' => (string)$post->getCreatedAt()
        ];
    }

    /**
     * Embed Comments
     *
     * @param  PostPresenter  $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeComments(PostPresenter $post)
    {
        $presenter = \App::make('MicheleAngioni\Support\Presenters\Presenter');

        $comments = $presenter->collection($post->comments, new CommentPresenter());

        return $this->collection($comments, new CommentTransformer);
    }

    /**
     * Embed Likes
     *
     * @param  PostPresenter  $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeLikes(PostPresenter $post)
    {
        return $this->collection($post->likes, new LikeTransformer);
    }

}
