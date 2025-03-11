<?php

use App\Models\Tag;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name']
        ]);
});

test('not fount tag', function() {
    $response = $this->getJson('/api/v1/tags/9999');
    $response->assertStatus(404)
        ->assertJsonStructure([
            'success',
            'message'
        ]);
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

    $response = $this->putJson("/api/v1/tags/{$tag->id}");

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
