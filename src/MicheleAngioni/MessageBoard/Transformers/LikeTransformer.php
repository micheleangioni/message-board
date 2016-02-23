<?php namespace MicheleAngioni\MessageBoard\Transformers;

use League\Fractal\TransformerAbstract;
use MicheleAngioni\MessageBoard\Models\Like;

class LikeTransformer extends TransformerAbstract
{
    /**
     * Turn input Player model into a generic array
     *
     * @param  Like  $like
     * @return array
     */
    public function transform(Like $like)
    {
        return [
            'id' => (int)$like->getKey(),
            'author_id' => $like->getAuthorId(),
            'author_name' => $like->getAuthor()->getUsername(),
            'owner_id' => $like->getOwnerId(),
            'owner_name' => $like->getOwner()->getUsername(),
            'createdAt' => $like->getCreatedAt()
        ];
    }

}
