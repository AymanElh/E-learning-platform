<?php

namespace App\Repositories\Content;

use App\Interfaces\Content\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::with('children')->get();
    }

    /**
     * Get category by ID
     *
     * @param int $id
     * @return \App\Models\Category
     */
    public function getById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    /**
     * Store a new category
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function store(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): bool
    {
        $category = Category::findOrFail($id);
        return $category->update($data);
    }

    /**
     * Delete a category
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $category = Category::findOrFail($id);

        // Check if category has children
        if ($this->hasChildren($id)) {
            throw new \Exception('Cannot delete category that has subcategories. Please delete or reassign subcategories first.');
        }

        // Check if category has courses
        if ($category->courses()->count() > 0) {
            throw new \Exception('Cannot delete category that has courses assigned to it. Please reassign courses first.');
        }

        return $category->delete();
    }

    /**
     * Check if category has children
     *
     * @param int $id
     * @return bool
     */
    public function hasChildren(int $id): bool
    {
        return Category::where('parent_id', $id)->exists();
    }

    /**
     * Get all child categories
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(int $id): \Illuminate\Database\Eloquent\Collection
    {
        return Category::where('parent_id', $id)->get();
    }
}
