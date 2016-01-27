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
     * If $category is FALSE, no check will be made on categories. If set to NULL, only Posts with no categories
     *  will be retrieved. Otherwise $category can be the Category id or name.
     * If $private is NULL, no checks will be made. If TRUE/FALSE, only private/public posts will be retrieved.
     * It also sets the child_datetime post attribute.
     *
     * @param  int  $idUser
     * @param  int|string|bool|null  $category
     * @param  bool|null  $private
     * @param  int  $page
     * @param  int  $limit
     * @throws \InvalidArgumentException
     * @throws \MicheleAngioni\Support\Exceptions\DatabaseException
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrderedPosts($idUser, $category, $private, $page, $limit);

}
