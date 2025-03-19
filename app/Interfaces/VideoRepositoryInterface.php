<?php

namespace App\Interfaces;

interface VideoRepositoryInterface
{
    public function addToCourse(int $courseId, array $data, $file);
    public function update(int $id, array $data, $file);
    public function delete(int $id);
    public function getVideosByCourse(int $courseId);
    public function getVideoById(int $id);
}
