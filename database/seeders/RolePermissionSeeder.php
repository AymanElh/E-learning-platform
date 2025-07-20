<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1);

        if(!$user) {
            return;
        }

        // create roles
        $admin = Role::create(['name' => 'admin']);

        // create permissions
        $createCategories = Permission::create(['name' => 'create categories']);
        $editCategories = Permission::create(['name' => 'edit categories']);
        $deleteCategories = Permission::create(['name' => 'delete categories']);

        // assign permission to roles
        $admin->syncPermissions([$createCategories, $editCategories, $deleteCategories]);

        $user->assignRole($admin);


    }
}

