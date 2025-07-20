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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by resource
        $permissions = [
            // Category permissions
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Course permissions
            'view-courses',
            'create-courses',
            'edit-courses',
            'delete-courses',
            'publish-courses',

            // Enrollment permissions
            'view-enrollments',
            'create-enrollments',
            'edit-enrollments',
            'delete-enrollments',

            // User management permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-roles',

            // Video permissions
            'view-videos',
            'create-videos',
            'edit-videos',
            'delete-videos',

            // Tag permissions
            'view-tags',
            'create-tags',
            'edit-tags',
            'delete-tags',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles with their permissions
        $roles = [
            'admin' => $permissions, // Admin gets all permissions
            'instructor' => [
                'view-categories',
                'view-courses',
                'create-courses',
                'edit-courses',
                'publish-courses',
                'view-videos',
                'create-videos',
                'edit-videos',
                'delete-videos',
                'view-tags',
                'create-tags',
                'edit-tags',
                'view-enrollments',
            ],
            'student' => [
                'view-categories',
                'view-courses',
                'create-enrollments',
                'view-enrollments',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
            $this->command->info("Created role: {$roleName} with " . count($rolePermissions) . " permissions");
        }

        // Create default admin user if it doesn't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'ayman@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role to the admin user
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
            $this->command->info("Assigned admin role to user: {$adminUser->email}");
        }

        // If there's already a first user, make them admin too (for existing projects)
        $firstUser = User::first();
        if ($firstUser && $firstUser->id !== $adminUser->id && !$firstUser->hasAnyRole()) {
            $firstUser->assignRole('admin');
            $this->command->info("Assigned admin role to first user: {$firstUser->email}");
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
