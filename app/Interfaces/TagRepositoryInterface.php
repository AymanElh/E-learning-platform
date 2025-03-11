<?php

namespace App\Interfaces;

interface TagRepositoryInterface
{
    public function index();
    public function store(array $data);
    public function getById(int $id);
    public function update(int $id, array $data);
    public function delete(int $id);

}
