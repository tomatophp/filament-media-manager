<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Models\Media;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    actingAs(User::factory()->create());

    $this->panel->resources([
        MediaResource::class,
    ]);

    Storage::fake('public');
});

it('can render media resource with folder id', function () {
    $folder = Folder::factory()->create([
        'is_protected' => false,
    ]);

    session()->put('folder_id', $folder->id);

    get(MediaResource::getUrl('index') . '?folder_id=' . $folder->id)
        ->assertSuccessful();
});

it('can list media items', function () {
    $folder = Folder::factory()->create();

    session()->put('folder_id', $folder->id);

    // Create test media items
    for ($i = 0; $i < 5; $i++) {
        Media::create([
            'model_type' => Folder::class,
            'model_id' => $folder->id,
            'name' => "test-file-{$i}",
            'file_name' => "test-{$i}.jpg",
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'collection_name' => $folder->collection,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);
    }

    expect(Media::where('collection_name', $folder->collection)->count())->toBe(5);
});

it('can handle media with custom properties', function () {
    $folder = Folder::factory()->create();

    $media = Media::create([
        'model_type' => Folder::class,
        'model_id' => $folder->id,
        'name' => 'test-file',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'collection_name' => $folder->collection,
        'manipulations' => [],
        'custom_properties' => [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'alt' => 'Test Alt',
        ],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->getCustomProperty('title'))->toBe('Test Title');
    expect($media->getCustomProperty('description'))->toBe('Test Description');
    expect($media->getCustomProperty('alt'))->toBe('Test Alt');
});

it('can edit media properties', function () {
    $folder = Folder::factory()->create();

    $media = Media::create([
        'model_type' => Folder::class,
        'model_id' => $folder->id,
        'name' => 'test-file',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'collection_name' => $folder->collection,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media->setCustomProperty('title', 'Updated Title');
    $media->setCustomProperty('description', 'Updated Description');
    $media->setCustomProperty('alt', 'Updated Alt');
    $media->save();

    expect($media->getCustomProperty('title'))->toBe('Updated Title');
    expect($media->getCustomProperty('description'))->toBe('Updated Description');
    expect($media->getCustomProperty('alt'))->toBe('Updated Alt');
});

it('can delete media', function () {
    $folder = Folder::factory()->create();

    $media = Media::create([
        'model_type' => Folder::class,
        'model_id' => $folder->id,
        'name' => 'test-file',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'collection_name' => $folder->collection,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $mediaId = $media->id;
    $media->delete();

    expect(Media::find($mediaId))->toBeNull();
});

it('can handle different mime types', function () {
    $folder = Folder::factory()->create();

    $mimeTypes = [
        'image/jpeg',
        'image/png',
        'video/mp4',
        'audio/mpeg',
        'application/pdf',
        'application/zip',
    ];

    foreach ($mimeTypes as $index => $mimeType) {
        $media = Media::create([
            'model_type' => Folder::class,
            'model_id' => $folder->id,
            'name' => "test-file-{$index}",
            'file_name' => "test-{$index}.ext",
            'mime_type' => $mimeType,
            'disk' => 'public',
            'size' => 1024,
            'collection_name' => $folder->collection,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);

        expect($media->mime_type)->toBe($mimeType);
    }
});
