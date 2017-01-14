<?php

namespace MicheleAngioni\MessageBoard\Services;

use Helpers;
use Illuminate\Support\Collection;
use MicheleAngioni\MessageBoard\Contracts\CategoryRepositoryInterface as CategoryRepo;
use MicheleAngioni\MessageBoard\Contracts\MbUserInterface;
use MicheleAngioni\MessageBoard\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MicheleAngioni\Support\Exceptions\PermissionsException;

class CategoryService
{
    protected $categoryRepo;

    public function __construct(CategoryRepo $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Return a collection of all available Categories.
     *
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categoryRepo->all();
    }

    /**
     * Return input Category.
     *
     * @param  int|string  $category
     * @throws ModelNotFoundException
     *
     * @return Category
     */
    public function getCategory($category)
    {
        if(Helpers::isInt($category, 1)) {
            return $this->categoryRepo->findOrFail($category);
        }
        else {
            return $this->categoryRepo->firstOrFailBy(['name' => $category]);
        }
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
     * @throws PermissionsException
     *
     * @return Category
     *
     */
    public function createCategory($name, $defaultPic = null, $private = false, MbUserInterface $user = null)
    {
        if($user) {
            if(!$user->canMb('Manage Categories')) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot manage Categories.");
            }
        }

        try {
            $category = $this->categoryRepo->create([
                'name' => $name,
                'default_pic' => $defaultPic,
                'private' => $private,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException("Caught RuntimeException in ".__METHOD__.' at line '.__LINE__.': ' .$e->getMessage());
        }

        return $category;
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
        if($user) {
            if(!$user->canMb('Manage Categories')) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot manage Categories.");
            }
        }

        $this->categoryRepo->updateById($idCategory, $attributes);

        return true;
    }

    /**
     * Delete input Category.
     * If $user is provided, it will be checked if he/she can manage Categories.
     *
     * @param  int  $idCategory
     * @param  MbUserInterface|null  $user
     * @throws PermissionsException
     *
     * @return bool
     */
    public function deleteCategory($idCategory, MbUserInterface $user = null)
    {
        if($user) {
            if(!$user->canMb('Manage Categories')) {
                throw new PermissionsException('Caught PermissionsException in ' . __METHOD__ . ' at line ' . __LINE__ . ': user ' . $user->getUsername() . " cannot manage Categories.");
            }
        }

        $category = $this->getCategory($idCategory);
        $category->delete();

        return true;
    }
}
