<?php

namespace App\Interfaces;

use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    /**
     * Get all roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get role by ID
     *
     * @param int $id
     * @return Role|null
     */
    public function getRoleById(int $id): ?Role;

    /**
     * Create a new role
     *
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role;

    /**x
     * Update role
     *
     * @param int $id
     * @param array $data
     * @return Role|null
     */
    public function updateRole(int $id, array $data): ?Role;

    /**
     * Delete role
     *
     * @param int $id
     * @return bool
     */
    public function deleteRole(int $id);

    /**
     * Get all permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Assign permissions to role
     *
     * @param int $roleId
     * @param array $permissions
     * @return Role|null
     */
    public function assignPermissions(int $roleId, array $permissions): ?Role;

    /**
     * Remove permissions from role
     *
     * @param int $roleId
     * @param array $permissions
     * @return Role|null
     */
    public function removePermissions(int $roleId, array $permissions): ?Role;
}
