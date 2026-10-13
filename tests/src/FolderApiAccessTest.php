<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();

    $this->private = Folder::factory()->create([
        'name' => 'owner private',
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => false,
    ]);
});

it('requires authentication', function () {
    getJson('/api/media-manager/folders')->assertUnauthorized();
    getJson('/api/media-manager/folders/' . $this->private->id)->assertUnauthorized();
});

it('lets the owner read their own folder', function () {
    actingAs($this->owner);

    getJson('/api/media-manager/folders/' . $this->private->id)
        ->assertOk()
        ->assertJsonPath('data.id', $this->private->id)
        ->assertJsonStructure(['data' => ['id', 'name', 'description', 'media', 'folders', 'created_at', 'updated_at']]);
});

it('hides another user private folder', function () {
    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $this->private->id)->assertNotFound();
});

it('does not list other users private folders', function () {
    $public = Folder::factory()->create([
        'name' => 'shared public',
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => true,
    ]);

    actingAs($this->other);

    $ids = collect(getJson('/api/media-manager/folders')->assertOk()->json('data'))->pluck('id');

    expect($ids)->toContain($public->id)->not->toContain($this->private->id);

    actingAs($this->owner);

    $ids = collect(getJson('/api/media-manager/folders')->assertOk()->json('data'))->pluck('id');

    expect($ids)->toContain($public->id)->toContain($this->private->id);
});

it('still serves public and unowned folders to any authenticated user', function () {
    $unowned = Folder::factory()->create();
    $public = Folder::factory()->create(['user_id' => $this->owner->id, 'user_type' => User::class, 'is_public' => true]);

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $unowned->id)->assertOk();
    getJson('/api/media-manager/folders/' . $public->id)->assertOk();
});

it('serves a folder that was shared with the user', function () {
    $shared = Folder::factory()->create([
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => false,
        'has_user_access' => true,
    ]);
    $shared->users()->attach($this->other->id);

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $shared->id)->assertOk();
});

it('denies a protected folder without the password', function () {
    $folder = Folder::factory()->protected('top-secret-123')->create();

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $folder->id)->assertForbidden();
});

it('denies a protected folder with a wrong password', function () {
    $folder = Folder::factory()->protected('top-secret-123')->create();

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $folder->id . '?password=nope')->assertForbidden();
    getJson('/api/media-manager/folders/' . $folder->id, ['X-Folder-Password' => 'nope'])->assertForbidden();
    getJson('/api/media-manager/folders/' . $folder->id . '?password[]=top-secret-123')->assertForbidden();
});

it('serves a protected folder with the correct password', function () {
    $folder = Folder::factory()->protected('top-secret-123')->create();

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $folder->id . '?password=top-secret-123')
        ->assertOk()
        ->assertJsonPath('data.id', $folder->id);

    getJson('/api/media-manager/folders/' . $folder->id, ['X-Folder-Password' => 'top-secret-123'])->assertOk();
});

it('never opens a protected folder that has no stored password', function () {
    $folder = Folder::factory()->create(['is_protected' => true, 'password' => '']);

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $folder->id)->assertForbidden();
});

it('still needs the user to be allowed to see a protected folder', function () {
    $folder = Folder::factory()->protected('top-secret-123')->create([
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => false,
    ]);

    actingAs($this->other);

    getJson('/api/media-manager/folders/' . $folder->id . '?password=top-secret-123')->assertNotFound();
});

it('does not list inaccessible sub folders', function () {
    $parent = Folder::factory()->create(['user_id' => $this->owner->id, 'user_type' => User::class, 'is_public' => true]);
    $visible = Folder::factory()->create([
        'model_id' => $parent->id,
        'model_type' => Folder::class,
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => true,
    ]);
    $hidden = Folder::factory()->create([
        'model_id' => $parent->id,
        'model_type' => Folder::class,
        'user_id' => $this->owner->id,
        'user_type' => User::class,
        'is_public' => false,
    ]);

    actingAs($this->other);

    $ids = collect(getJson('/api/media-manager/folders/' . $parent->id)->assertOk()->json('data.folders'))->pluck('id');

    expect($ids)->toContain($visible->id)->not->toContain($hidden->id);
});
