<?php

namespace App\Repositories;

use App\Interfaces\PermissionRepositoryInterface;
use phpDocumentor\Reflection\Types\Collection;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * Get all permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        return Permission::all();
    }

    /**
     * Get permission by ID
     *
     * @param int $id
     * @return \Spatie\Permission\Models\Permission|null
     */
    public function getPermissionById(int $id)
    {
        return Permission::find($id);
    }

    /**
     * Create a new permission
     *
     * @param array $data
     * @return \Spatie\Permission\Models\Permission
     */
    public function createPermission(array $data)
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'api'
        ]);
    }

    /**
     * Update permission
     *
     * @param int $id
     * @param array $data
     * @return \Spatie\Permission\Models\Permission|null
     */
    public function updatePermission(int $id, array $data)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return null;
        }

        $permission->name = $data['name'] ?? $permission->name;
        $permission->save();

        return $permission;
    }

    /**
     * Delete permission
     *
     * @param int $id
     * @return bool
     */
    public function deletePermission(int $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return false;
        }

        return $permission->delete();
    }
}
