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

    $this->normalUser = User::factory()->create();

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

// READ OPERATIONS
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
        ])
        ->assertJson([
            'success' => true
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
                'parentId',
                'description'
            ]
        ])
        ->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => $category->name
            ]
        ]);
});

test('returns 404 for non-existent category', function() {
    $response = $this->getJson('/api/v1/categories/9999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => "Category not found"
        ]);
});

// CREATE OPERATIONS
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
        'parent_id' => $parent->id
    ];

    $response = $this->postJson('/api/v1/categories', $data);
//    dd($response->json());
    $response->assertStatus(201)
        ->assertJson([
            'success' => true
        ]);

    $this->assertDatabaseHas('categories', $data);

    // Verify the relationship
    $subcategory = Category::where('name', 'Smartphones')->first();
    expect($subcategory->parent_id)->toBe($parent->id);
});

test('cannot create category with invalid parent', function() {
    $data = [
        'name' => 'Test Category',
        'description' => 'Test description',
        'parent_id' => 9999 // Non-existent parent
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);
});

test('cannot create category without required fields', function() {
    $response = $this->postJson('/api/v1/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('cannot create category with duplicate name', function() {
    $existingCategory = Category::factory()->create(['name' => 'Unique Name']);

    $data = [
        'name' => 'Unique Name', // Duplicate name
        'description' => 'Different description'
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

// UPDATE OPERATIONS
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

test('cannot update category to duplicate name', function() {
    $existingCategory = Category::factory()->create(['name' => 'Existing Name']);
    $categoryToUpdate = Category::factory()->create(['name' => 'Original Name']);

    $response = $this->putJson("/api/v1/categories/{$categoryToUpdate->id}", [
        'name' => 'Existing Name' // Trying to use existing name
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('returns 404 when updating non-existent category', function() {
    $response = $this->putJson('/api/v1/categories/9999', [
        'name' => 'Updated Name'
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Category not found',
        ]);
});

// DELETE OPERATIONS
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

test('returns 404 when deleting non-existent category', function() {
    $response = $this->deleteJson('/api/v1/categories/9999');

    $response->assertStatus(404);
});

test('cannot delete category that has children', function() {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    $response = $this->deleteJson("/api/v1/categories/{$parent->id}");

    // This should either fail or cascade delete - test based on your business logic
    $response->assertStatus(400); // Adjust based on your implementation
    $this->assertDatabaseHas('categories', ['id' => $parent->id]);
});

// RELATIONSHIP OPERATIONS
test('can get children categories', function() {
    $parent = Category::factory()->create();
    $children = Category::factory(3)->create([
        'parent_id' => $parent->id
    ]);

    $response = $this->getJson("/api/v1/categories/{$parent->id}/children");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'categories' => [
                '*' => [
                    'id', 'name', 'parentId', 'description'
                ]
            ]
        ])
        ->assertJson([
            'success' => true
        ]);

    expect($response['categories'])->toHaveCount(3);
});

test('returns empty array for category with no children', function() {
    $category = Category::factory()->create();

    $response = $this->getJson("/api/v1/categories/{$category->id}/children");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'categories' => []
        ]);
});

// AUTHORIZATION TESTS
test('unauthorized user cannot create category', function() {
    $this->actingAs($this->normalUser, 'api');

    $data = [
        'name' => 'Test Category',
        'description' => 'Test description'
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(403);
});

test('unauthorized user cannot update category', function() {
    $category = Category::factory()->create();
    $this->actingAs($this->normalUser, 'api');

    $response = $this->putJson("/api/v1/categories/{$category->id}", [
        'name' => 'Updated Name'
    ]);

    $response->assertStatus(403);
});

test('unauthorized user cannot delete category', function() {
    $category = Category::factory()->create();
    $this->actingAs($this->normalUser, 'api');

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(403);
});


// EDGE CASES
test('can handle long category names', function() {
    $longName = str_repeat('A', 255); // Adjust based on your database constraints

    $data = [
        'name' => $longName,
        'description' => 'Test description'
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', ['name' => $longName]);
});

test('can handle special characters in category name', function() {
    $specialName = 'Special & Characters: @#$%';

    $data = [
        'name' => $specialName,
        'description' => 'Test description'
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', ['name' => $specialName]);
});

test('can create category with null description', function() {
    $data = [
        'name' => 'No Description Category'
        // description is optional
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', [
        'name' => 'No Description Category',
        'description' => null
    ]);
});
