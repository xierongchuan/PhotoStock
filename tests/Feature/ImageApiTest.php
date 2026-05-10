<?php

use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('local');
});

test('authenticated user can upload a jpeg image', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/images', [
        'image' => UploadedFile::fake()->image('photo.jpg')->size(200),
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.original_name', 'photo.jpg');

    expect(Image::count())->toBe(1);
    Storage::disk('local')->assertExists(Image::first()->storage_path);
});

test('upload rejects unsupported files', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/images', [
        'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ]);

    $response->assertUnprocessable();
});

test('upload rejects files larger than five megabytes', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/images', [
        'image' => UploadedFile::fake()->image('large.png')->size(6000),
    ]);

    $response->assertUnprocessable();
});

test('image list contains only current users images', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownersImage = Image::create([
        'user_id' => $owner->id,
        'original_name' => 'owner.jpg',
        'storage_path' => 'images/owner.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
    ]);

    Image::create([
        'user_id' => $otherUser->id,
        'original_name' => 'other.jpg',
        'storage_path' => 'images/other.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 2048,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->getJson('/api/images');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownersImage->id);
});

test('user cannot view another users image', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $image = Image::create([
        'user_id' => $owner->id,
        'original_name' => 'owner.jpg',
        'storage_path' => 'images/owner.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
    ]);

    Storage::disk('local')->put($image->storage_path, 'fake-image-content');
    Sanctum::actingAs($intruder);

    $this->get("/api/images/{$image->id}")->assertForbidden();
});

test('user cannot delete another users image', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $image = Image::create([
        'user_id' => $owner->id,
        'original_name' => 'owner.jpg',
        'storage_path' => 'images/owner.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
    ]);

    Storage::disk('local')->put($image->storage_path, 'fake-image-content');
    Sanctum::actingAs($intruder);

    $this->deleteJson("/api/images/{$image->id}")->assertForbidden();
    expect(Image::count())->toBe(1);
});

test('deleting image removes database record and physical file', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $image = Image::create([
        'user_id' => $user->id,
        'original_name' => 'owner.jpg',
        'storage_path' => 'images/owner.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
    ]);

    Storage::disk('local')->put($image->storage_path, 'fake-image-content');

    $this->deleteJson("/api/images/{$image->id}")->assertNoContent();

    expect(Image::count())->toBe(0);
    Storage::disk('local')->assertMissing('images/owner.jpg');
});
