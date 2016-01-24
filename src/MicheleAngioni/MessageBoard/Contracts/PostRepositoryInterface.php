<?php namespace MicheleAngioni\MessageBoard\Contracts;

interface PostRepositoryInterface {

    /**
     * Delete mb posts of input date, older than input datetime of format 'Y-m-d H:i:s' .
     * Return true on success.
     *
     * @param  string  $type
     * @param  string  $datetime
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function deleteOldMessages($type, $datetime);

    /**
     * Return posts of input type of the input user, ordered by post AND comment datetime.
     * It also sets the child_datetime post attribute.
     *
     * @param  int  $idUser
     * @param  string  $messageType
     * @param  int  $page
     * @param  int  $limit
     * @throws \InvalidArgumentException
     * @throws \MicheleAngioni\Support\Exceptions\DatabaseException
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrderedPosts($idUser, $messageType, $page, $limit);

}
