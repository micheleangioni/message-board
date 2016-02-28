<?php namespace MicheleAngioni\MessageBoard;

use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Contracts\MbGatewayInterface;
use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Services\CategoryService;
use MicheleAngioni\MessageBoard\Services\MessageBoardService;

abstract class AbstractMbGateway implements MbGatewayInterface {

    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @var MessageBoardService
     */
    protected $messageBoardService;

    /**
     * Standard number of mb posts per page.
     *
     * @var int
     */
    protected $postsPerPage;


    function __construct(CategoryService $categoryService, MessageBoardService $messageBoardService)
    {
        $this->categoryService = $categoryService;

        $this->messageBoardService = $messageBoardService;
    }

    /**
     * Return a collection of all available Categories.
     *
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categoryService->getCategories();
    }

    /**
     * Return input Category.
     *
     * @param  int|string  $category
     * @return \MicheleAngioni\MessageBoard\Models\Category
     */
    public function getCategory($category)
    {
        return $this->categoryService->getCategory($category);
    }

    /**
     * Create a new Category.
     * If $user is provided, it will be checked if he/she can manage Categories.
     *
     *
     * @param  string  $name
     * @param  string|null  $defaultPic
     * @param  bool  $private
     * @param  MbUserInterface|null  $user
     * @throws \MicheleAngioni\Support\Exceptions\PermissionsException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Category
     *
     */
    public function createCategory($name, $defaultPic = null, $private = false, MbUserInterface $user = null)
    {
        return $this->categoryService->createCategory($name, $defaultPic, $private, $user);
    }

    /**
     * Update input Category.
     * If $user is provided, it will be checked if he/she can manage Categories.
     * Return true on success.
     *
     * @param  int  $idCategory
     * @param  array  $attributes
     * @param  MbUserInterface|null  $user
     *
     * @return bool
     */
    public function updateCategory($idCategory, array $attributes, MbUserInterface $user = null)
    {
        return $this->categoryService->updateCategory($idCategory, $attributes, $user);
    }

    /**
     * Delete input Category.
     * If $user is provided, it will be checked if he/she can manage Categories.
     *
     * @param  int  $idCategory
     * @param  MbUserInterface|null  $user
     * @throws \MicheleAngioni\Support\Exceptions\PermissionsException
     *
     * @return bool
     */
    public function deleteCategory($idCategory, MbUserInterface $user = null)
    {
        return $this->categoryService->deleteCategory($idCategory, $user);
    }


    /**
     * Create the coded text of the mb post.
     * Keys in the messageboard.php lang file will be used for localization.
     *
     * @param  string  $code
     * @param  MbUserInterface  $user
     * @param  array  $attributes
     *
     * @return string
     */
    public function getCodedPostText($code, MbUserInterface $user, array $attributes = [])
    {
        return $this->messageBoardService->getCodedPostText($code, $user, $attributes);
    }

    /**
     * Create a new Post from a coded text.
     *
     * @param  MbUserInterface  $user
     * @param  int|null  $categoryId
     * @param  string  $code
     * @param  array  $attributes
     *
     * @return \MicheleAngioni\MessageBoard\Models\Post
     */
    public function createCodedPost(MbUserInterface $user, $categoryId = null, $code, array $attributes = [])
    {
        return $this->messageBoardService->createCodedPost($user, $categoryId, $code, $attributes);
    }

    /**
     * Create a new Post. By default, check if the poster is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  MbUserInterface|null  $poster
     * @param  int|null  $categoryId
     * @param  string  $text
     * @param  bool  $banCheck
     * @throws \InvalidArgumentException
     * @throws \MicheleAngioni\Support\Exceptions\PermissionsException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Post
     */
    public function createPost(MbUserInterface $user, MbUserInterface $poster = null, $categoryId = null, $text, $banCheck = true)
    {
        return $this->messageBoardService->createPost($user, $poster, $categoryId, $text, $banCheck);
    }

    /**
     * Retrieve and return input Post.
     *
     * @param  int  $idPost
     * @param  MbUserInterface  $user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Post
     */
    public function getPost($idPost, MbUserInterface $user = null)
    {
        return $this->messageBoardService->getPost($idPost, $user);
    }

    /**
     * Updated text of input Post.
     * If a User is provided as third argument, a check will be performed if he/she can delete the Post.
     * Return bool on success.
     *
     * @param  int  $idPost
     * @param  string  $text
     * @param  MbUserInterface  $user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Post
     */
    public function updatePost($idPost, $text, MbUserInterface $user = null)
    {
        return $this->messageBoardService->updatePost($idPost, $text, $user);
    }

    /**
     * Delete input Post. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Post.
     *
     * @param  int  $idPost
     * @param  MbUserInterface  $user
     *
     * @return bool
     */
    public function deletePost($idPost, MbUserInterface $user = null)
    {
        return $this->messageBoardService->deletePost($idPost, $user);
    }

    /**
     * Create a new Comment. Check if the user is actually banned.
     *
     * @param  MbUserInterface  $user
     * @param  int  $postId
     * @param  string  $text
     * @param  bool  $banCheck = true
     *
     * @return \MicheleAngioni\MessageBoard\Models\Comment
     */
    public function createComment(MbUserInterface $user, $postId, $text, $banCheck = true)
    {
        return $this->messageBoardService->createComment($user, $postId, $text, $banCheck);
    }

    /**
     * Retrieve and return input Comment.
     *
     * @param  int  $idComment
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Comment
     */
    public function getComment($idComment)
    {
        return $this->messageBoardService->getComment($idComment);
    }

    /**
     * Updated text of input Comment. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Comment.
     *
     * @param  int  $idComment
     * @param  string  $text
     * @param  MbUserInterface  $user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return bool
     */
    public function updateComment($idComment, $text, MbUserInterface $user = null)
    {
        return $this->messageBoardService->updateComment($idComment, $text, $user);
    }

    /**
     * Delete input Comment. Return true on success.
     * If a User is provided as second argument, a check will be performed if he/she can delete the Comment.
     *
     * @param  int  $idComment
     * @param  MbUserInterface  $user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return bool
     */
    public function deleteComment($idComment, MbUserInterface $user = null)
    {
        return $this->messageBoardService->deleteComment($idComment, $user);
    }

    /**
     * Assign a new Like to input entity and user.
     *
     * @param  int  $idUser
     * @param  int  $likableEntityId
     * @param  string  $likableEntity
     * @throws \InvalidArgumentException
     *
     * @return \MicheleAngioni\MessageBoard\Models\Like
     */
    public function createLike($idUser, $likableEntityId, $likableEntity)
    {
        return $this->messageBoardService->createLike($idUser, $likableEntityId, $likableEntity);
    }

    /**
     * Delete input like. Return true on success.
     *
     * @param  int  $idUser
     * @param  int  $likableEntityId
     * @param  string  $likableEntity
     *
     * @return bool
     */
    public function deleteLike($idUser, $likableEntityId, $likableEntity)
    {
        return $this->messageBoardService->deleteLike($idUser, $likableEntityId, $likableEntity);
    }

    /**
     * Update input user last view.
     *
     * @param  MbUserInterface  $user
     */
    public function updateUserLastView(MbUserInterface $user)
    {
        $this->messageBoardService->updateUserLastView($user);
    }

    /**
     * Return posts of input type on the input user mb, ordered by post AND comment datetime.
     * Optionally the posts can be passed to the presenter and the text escaped.
     * If $category is FALSE, no check will be made on categories. If set to NULL, only Posts with no categories
     *  will be retrieved. Otherwise $category can be the Category id or name.
     * If $private is NULL, no checks will be made. If TRUE/FALSE, only private/public posts will be retrieved.
     * If the user requesting the post is not the owner of the message board, it can be specified among the inputs.
     *
     * @param  MbUserInterface  $user
     * @param  int|string|bool|null  $category
     * @param  bool|null  $private
     * @param  int  $page = 1
     * @param  int  $limit = 2
     * @param  bool  $applyPresenter = false
     * @param  bool  $escapeText = false
     * @param  MbUserInterface|bool  $userVisiting
     * @throws \InvalidArgumentException
     *
     * @return Collection
     */
    public function getOrderedUserPosts(MbUserInterface $user, $category = false, $private = null, $page = 1, $limit = 20,
                                        $applyPresenter = false, $escapeText = false, MbUserInterface $userVisiting = null)
    {
        return $this->messageBoardService->getOrderedUserPosts($user, $category, $private, $page, $limit,
                                                                $applyPresenter, $escapeText, $userVisiting);
    }

    /**
     * Pass a Post or Comment model to the corresponding presenter and return it
     *
     * @param  MbUserInterface  $user
     * @param  \MicheleAngioni\MessageBoard\Models\Post|\MicheleAngioni\MessageBoard\Models\Comment  $model
     * @param  bool  $escapeText = false
     * @throws \InvalidArgumentException
     *
     * @return \MicheleAngioni\MessageBoard\Presenters\PostPresenter|\MicheleAngioni\MessageBoard\Presenters\CommentPresenter
     */
    public function presentModel(MbUserInterface $user, $model, $escapeText = false)
    {
        return $this->messageBoardService->presentModel($user, $model, $escapeText);
    }

    /**
     * Pass a Post or Comment collection to the corresponding presenter and return it
     *
     * @param  MbUserInterface  $user
     * @param  Collection  $collection
     * @param  bool  $escapeText = false
     * @throws \InvalidArgumentException
     *
     * @return Collection
     */
    public function presentCollection(MbUserInterface $user, Collection $collection, $escapeText = false)
    {
        return $this->messageBoardService->presentCollection($user, $collection, $escapeText);
    }

    /**
     * Ban input user. In the user is already banned, extend the banning days.
     * Return true on success.
     *
     * @param  MbUserInterface  $user
     * @param  int  $days
     * @param  string  $reason = ''
     *
     * @return bool
     */
    public function banUser(MbUserInterface $user, $days, $reason = '')
    {
        return $this->messageBoardService->banUser($user, $days, $reason);
    }

}
