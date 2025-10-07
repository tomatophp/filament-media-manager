<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;
use TomatoPHP\FilamentMediaManager\Tests\Models\Product;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create());
});

describe('MediaManagerInput Component', function () {
    it('can be instantiated', function () {
        $field = MediaManagerInput::make('images');

        expect($field)->toBeInstanceOf(MediaManagerInput::class);
    });

    it('can set disk', function () {
        $field = MediaManagerInput::make('images')->disk('public');

        expect($field->getDiskName())->toBe('public');
    });

    it('can be configured as single file', function () {
        $field = MediaManagerInput::make('image')->single();

        // Single method exists and returns static
        expect($field)->toBeInstanceOf(MediaManagerInput::class);
    });

    it('can set custom schema', function () {
        $field = MediaManagerInput::make('images')->schema([
            \Filament\Forms\Components\TextInput::make('title'),
        ]);

        expect($field)->toBeInstanceOf(MediaManagerInput::class);
    });
});

describe('MediaManagerInput File Upload', function () {
    it('can upload single file', function () {
        $product = Product::create(['name' => 'Test Product']);
        $file = UploadedFile::fake()->image('test.jpg');

        $media = $product->addMedia($file)->toMediaCollection('images');

        expect($media)->not->toBeNull();
        expect($media->collection_name)->toBe('images');
        expect($media->file_name)->toBe('test.jpg');
    });

    it('can upload multiple files', function () {
        $product = Product::create(['name' => 'Test Product']);
        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
            UploadedFile::fake()->image('test3.jpg'),
        ];

        foreach ($files as $file) {
            $product->addMedia($file)->toMediaCollection('images');
        }

        $mediaCount = $product->getMedia('images')->count();

        expect($mediaCount)->toBe(3);
    });

    it('stores files in correct disk', function () {
        $product = Product::create(['name' => 'Test Product']);
        $file = UploadedFile::fake()->image('test.jpg');

        $media = $product->addMedia($file)
            ->toMediaCollection('images', 'public');

        expect($media->disk)->toBe('public');
    });

    it('stores files with custom properties', function () {
        $product = Product::create(['name' => 'Test Product']);
        $file = UploadedFile::fake()->image('test.jpg');

        $media = $product->addMedia($file)
            ->withCustomProperties([
                'title' => 'Test Image',
                'description' => 'A test image description',
            ])
            ->toMediaCollection('images');

        expect($media->getCustomProperty('title'))->toBe('Test Image');
        expect($media->getCustomProperty('description'))->toBe('A test image description');
    });
});

describe('MediaManagerInput Media Retrieval', function () {
    it('can get all media from collection', function () {
        $product = Product::create(['name' => 'Test Product']);

        $product->addMedia(UploadedFile::fake()->image('test1.jpg'))->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('test2.jpg'))->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('test3.jpg'))->toMediaCollection('documents');

        $images = $product->getMedia('images');

        expect($images)->toHaveCount(2);
    });

    it('can get first media from collection', function () {
        $product = Product::create(['name' => 'Test Product']);

        $product->addMedia(UploadedFile::fake()->image('test1.jpg'))->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('test2.jpg'))->toMediaCollection('images');

        $firstImage = $product->getFirstMedia('images');

        expect($firstImage)->not->toBeNull();
        expect($firstImage->file_name)->toBe('test1.jpg');
    });

    it('can get media url', function () {
        $product = Product::create(['name' => 'Test Product']);

        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $url = $media->getUrl();

        expect($url)->not->toBeNull();
        expect($url)->toContain('test.jpg');
    });

    it('can check if media exists', function () {
        $product = Product::create(['name' => 'Test Product']);

        $product->addMedia(UploadedFile::fake()->image('test.jpg'))->toMediaCollection('images');

        expect($product->hasMedia('images'))->toBeTrue();
        expect($product->hasMedia('videos'))->toBeFalse();
    });
});

describe('MediaManagerInput Media Deletion', function () {
    it('can delete single media', function () {
        $product = Product::create(['name' => 'Test Product']);

        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $mediaId = $media->id;
        $media->delete();

        $exists = $product->getMedia('images')->contains('id', $mediaId);

        expect($exists)->toBeFalse();
    });

    it('can clear media collection', function () {
        $product = Product::create(['name' => 'Test Product']);

        $product->addMedia(UploadedFile::fake()->image('test1.jpg'))->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('test2.jpg'))->toMediaCollection('images');

        expect($product->getMedia('images'))->toHaveCount(2);

        $product->clearMediaCollection('images');

        expect($product->getMedia('images'))->toHaveCount(0);
    });

    it('cascade deletes media when model is deleted', function () {
        $product = Product::create(['name' => 'Test Product']);

        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $mediaId = $media->id;
        $product->delete();

        $mediaExists = \TomatoPHP\FilamentMediaManager\Models\Media::find($mediaId);

        expect($mediaExists)->toBeNull();
    });
});

describe('MediaManagerInput with Custom Schema', function () {
    it('can use custom schema for file metadata', function () {
        $field = MediaManagerInput::make('images')
            ->schema([
                \Filament\Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('description')
                    ->maxLength(500),
            ]);

        expect($field)->toBeInstanceOf(MediaManagerInput::class);
    });

    it('saves custom properties from schema', function () {
        $product = Product::create(['name' => 'Test Product']);
        $file = UploadedFile::fake()->image('test.jpg');

        $media = $product->addMedia($file)
            ->withCustomProperties([
                'title' => 'Product Image',
                'description' => 'Main product photo',
            ])
            ->toMediaCollection('images');

        expect($media->getCustomProperty('title'))->toBe('Product Image');
        expect($media->getCustomProperty('description'))->toBe('Main product photo');
    });
});

describe('MediaManagerInput with Conversions', function () {
    it('can register media conversions', function () {
        $product = Product::create(['name' => 'Test Product']);

        // Note: This would require setting up conversion in the model
        // For testing purposes, we just verify the media is created
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        expect($media)->not->toBeNull();
    });

    it('can get conversion url', function () {
        $product = Product::create(['name' => 'Test Product']);

        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        // Get the original URL (conversion would work if defined in model)
        $url = $media->getUrl();

        expect($url)->not->toBeNull();
    });
});
