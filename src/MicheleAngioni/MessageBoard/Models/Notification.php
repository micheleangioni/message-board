<?php namespace MicheleAngioni\MessageBoard\Models;

class Notification extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tb_messboard_notifications';

    protected $guarded = array('id');


    // Relationships

    public function user()
    {
        return $this->belongsTo(\Config::get('ma_messageboard.model'));
    }


    // Getters

    /**
     * Return sender id.
     *
     * @return int
     */
    public function getFromId()
    {
        return $this->from_id;
    }

    /**
     * Return sender type.
     *
     * @return string
     */
    public function getFromType()
    {
        return $this->from_type;
    }

    /**
     * Return receiver id.
     *
     * @return int
     */
    public function getToId()
    {
        return $this->to_id;
    }

    /**
     * Return type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Return the url of the associated picture.
     *
     * @return string
     */
    public function getPicUrl()
    {
        return $this->pic_url;
    }

    /**
     * Return the url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return the extra data.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }


    // Other Methods

    /**
     * Return the purified Notification text.
     *
     * @return string
     */
    public function getCleanText()
    {
        return clean($this->getText(), 'ma_messageboard.mb_purifier_conf');
    }

    /**
     * Check if the Notification hasbeen read.
     *
     * @return bool
     */
    public function isRead()
    {
        if($this->read) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Set notification as read.
     */
    public function setAsRead()
    {
        $this->read = true;
        $this->save();
    }

}
