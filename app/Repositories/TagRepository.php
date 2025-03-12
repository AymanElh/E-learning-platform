<?php

namespace App\Repositories;

use App\Interfaces\TagRepositoryInterface;
use App\Models\Tag;

class TagRepository implements TagRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index(): \Illuminate\Database\Eloquent\Collection
    {
        return Tag::all();
    }

    public function getById(int $id)
    {
        return Tag::findOrFail($id);
    }

    public function store(array $data)
    {
        return Tag::create($data);
    }

    public function update(int $id, array $data)
    {
        return Tag::where('id', $id)->update($data);
    }

    public function delete(int $id)
    {
        return Tag::destroy($id);
    }
}
