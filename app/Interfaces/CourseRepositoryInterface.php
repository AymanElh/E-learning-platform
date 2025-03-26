<?php

namespace App\Interfaces;

interface CourseRepositoryInterface
{
    public function getAll(array $filters = []);
    public function getById(int $id);
    public function store(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function attachTags(int $id, array $tagIds);
    public function syncTags(int $id, array $tagIds);
    public function detachTags(int $id, array $tagIds);
}
