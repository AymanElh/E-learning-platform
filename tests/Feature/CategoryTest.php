<?php

use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $createPermission = Permission::firstOrCreate(['name' => 'create categories']);
    $editPermission = Permission::firstOrCreate(['name' => 'edit categories']);
    $deletePermission = Permission::firstOrCreate(['name' => 'delete categories']);

    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->syncPermissions([$createPermission, $editPermission, $deletePermission]);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

test('can get all categories', function() {
    Category::factory(5)->create();

    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'categories' => [
                '*' => ['id', 'name']
            ]
        ]);
});

test('can fetch one category', function() {
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'categoryId',
                'description'
            ]
        ]);
});

test('not found category', function() {
    $response = $this->getJson('/api/v1/categories/9999');
    $response->assertStatus(404);
});

test('can create a category', function() {
    $data = [
        'name' => 'Electronics',
        'description' => 'Electronic devices and gadgets'
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => "Category created successfully"
        ]);

    $this->assertDatabaseHas('categories', $data);
});

test('can create a subcategory', function() {
    $parent = Category::factory()->create();
    $data = [
        'name' => 'Smartphones',
        'description' => 'Mobile phones',
        'category_id' => $parent->id
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', $data);
});

test('can update category', function() {
    $category = Category::factory()->create();
    $update = [
        'name' => 'Updated Category',
        'description' => 'Updated description'
    ];

    $response = $this->putJson("/api/v1/categories/{$category->id}", $update);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Category updated successfully"
        ]);

    $this->assertDatabaseHas('categories', ['id' => $category->id] + $update);
});

test('can delete category', function() {
    $category = Category::factory()->create();
    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Category deleted successfully"
        ]);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('can get children categories', function() {
    $parent = Category::factory()->create();
    $children = Category::factory(3)->create([
        'category_id' => $parent->id
    ]);

    $response = $this->getJson("/api/v1/categories/{$parent->id}/children");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'categories' => [
                '*' => [
                    'id', 'name', 'categoryId', 'description'
                ]
            ]
        ]);

    $this->assertCount(3, $response['categories']);
});
