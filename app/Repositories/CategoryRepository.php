<?php

namespace App\Repositories;

use App\Filters\V1\CategoryFilter;
use App\Interfaces\CategoryRepositoryInterface;
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

    public function index(array $data)
    {
        $filter = new CategoryFilter();
        $queryItems = $filter->transform($data);
        if(count($queryItems) == 0) {
            return Category::paginate();
        } else {
            return Category::where($queryItems)->paginate();
        }
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
     */
    public function update(int $id, array $data): bool
    {
        return Category::where('id', $id)->update($data);
    }

    /**
     * Delete a category
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Category::destroy($id);
    }

    /**
     * Get all child categories
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(int $id): \Illuminate\Database\Eloquent\Collection
    {
        return Category::where('category_id', $id)->get();
    }
}
