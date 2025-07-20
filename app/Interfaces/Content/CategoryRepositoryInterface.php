<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index();

    /**
     * Get category by ID
     *
     * @param int $id
     * @return \App\Models\Category
     */
    public function getById(int $id);

    /**
     * Store a new category
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function store(array $data);

    /**
     * Update a category
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data);

    /**
     * Delete a category
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id);

    /**
     * Get all child categories
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(int $id);
}
