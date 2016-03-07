<?php

namespace MicheleAngioni\MessageBoard\Models;

class Category extends \Illuminate\Database\Eloquent\Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_categories';

    protected $guarded = array('id');

    public function posts()
    {
        return $this->hasMany('\MicheleAngioni\MessageBoard\Models\Post');
    }


    /**
     * Override the standard delete, deleting all related posts.
     */
    public function delete()
    {
        // Delete related posts
        foreach($this->posts as $post) {
            $post->delete();
        }

        parent::delete();
    }


    // Getters

    public function getName()
    {
        return $this->name;
    }

    public function getDefaultPic()
    {
        return $this->default_pic;
    }

    public function getPrivate()
    {
        return $this->private;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }


    // Other Methods

    /**
     * Check if the Category generates private posts.
     *
     * @return bool
     */
    public function isPrivate()
    {
        if($this->private) {
            return true;
        }
        else {
            return false;
        }
    }
}
