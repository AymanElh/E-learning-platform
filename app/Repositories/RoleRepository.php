<?php

namespace App\Repositories;

use App\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * @inheritDoc
     */
    public function getRoleById(int $id): ?\Spatie\Permission\Models\Role
    {
        return Role::with('permissions')->find($id);
    }

    /**
     * @inheritDoc
     */
    public function createRole(array $data): \Spatie\Permission\Models\Role
    {
        $role = Role::create(['name' => $data['name']]);

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * @inheritDoc
     */
    public function updateRole(int $id, array $data): ?\Spatie\Permission\Models\Role
    {
        $role = Role::find($id);
        if(!$role) {
            return null;
        }

        if (isset($data['name'])) {
            $role->name = $data['name'];
            $role->save();
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    /**
     * @inheritDoc
     */
    public function deleteRole(int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return false;
        }

        return $role->delete();
    }

    /**
     * @inheritDoc
     */
    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::all();
    }

    /**
     * @inheritDoc
     */
    public function assignPermissions(int $roleId, array $permissions): ?\Spatie\Permission\Models\Role
    {
        $role = Role::find($roleId);
        if(!$role) {
            return false;
        }

        $role->givePermissionTo($permissions);
        return $role->load('permissions');
    }

    /**
     * @inheritDoc
     */
    public function removePermissions(int $roleId, array $permissions): ?\Spatie\Permission\Models\Role
    {
        $role = Role::find($roleId);

        if (!$role) {
            return null;
        }

        $role->revokePermissionTo($permissions);

        return $role->load('permissions');
    }
}
