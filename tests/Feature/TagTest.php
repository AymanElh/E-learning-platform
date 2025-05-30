<?php

use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function() {
    $createPermission = Permission::firstOrCreate(['name' => 'create tags']);
    $editPermission = Permission::firstOrCreate(['name' => 'edit tags']);
    $deletePermission = Permission::firstOrCreate(['name' => 'delete tags']);

    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->syncPermissions([$createPermission, $editPermission, $deletePermission]);

    $this->user = User::factory()->create();
    $this->user->assignRole('admin');

    $this->token = auth()->login($this->user);

    $this->actingAs($this->user, 'api');
});

test('can get all tags', function() {
    Tag::factory(5)->create();

    $response = $this->getJson('/api/v1/tags');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'tags' => [
                '*' => ['id', 'name']
            ]
        ]);
});


test('can fetch on tag', function() {
    $tag = Tag::factory()->create();

    $response = $this->getJson("/api/v1/tags/{$tag->id}");
//    $response->dump();
    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name'
        ]);
});

test('not found tag', function() {
    $response = $this->getJson('/api/v1/tags/9999');
    $response->assertStatus(404);
});


test('can create a tag', function() {
    $data = ['name' => 'laravel'];
    $response = $this->postJson('/api/v1/tags', $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => "Tag inserted successfully"
        ]);

    $this->assertDatabaseHas('tags', $data);
});

test('can update tag', function() {
    $tag = Tag::factory()->create();
    $update = ['name' => 'ayman'];

    $response = $this->putJson("/api/v1/tags/{$tag->id}", $update);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Tag updated successfully"
        ]);

    $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'ayman']);
});


test('can delete tag', function() {
    $tag = Tag::factory()->create();
    $response = $this->deleteJson("/api/v1/tags/{$tag->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => "Tag deleted successfully"
        ]);

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});
