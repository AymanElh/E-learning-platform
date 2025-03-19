<?php

namespace App\Interfaces;

interface PermissionRepositoryInterface
{
    /**
     * Get all permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions();

    /**
     * Get permission by ID
     *
     * @param int $id
     * @return \Spatie\Permission\Models\Permission|null
     */
    public function getPermissionById(int $id);

    /**
     * Create a new permission
     *
     * @param array $data
     * @return \Spatie\Permission\Models\Permission
     */
    public function createPermission(array $data);

    /**
     * Update permission
     *
     * @param int $id
     * @param array $data
     * @return \Spatie\Permission\Models\Permission|null
     */
    public function updatePermission(int $id, array $data);

    /**
     * Delete permission
     *
     * @param int $id
     * @return bool
     */
    public function deletePermission(int $id);
}
