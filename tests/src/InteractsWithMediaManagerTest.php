<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Tests\Models\Product;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create());
});

describe('InteractsWithMediaManager Trait', function () {
    it('can get media manager media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        // Attach via pivot table
        DB::table('media_has_models')->insert([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'media_id' => $media->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $product->getMediaManagerMedia();

        expect($result)->toHaveCount(1);
        expect($result->first()->uuid)->toBe($media->uuid);
    });

    it('returns empty collection when no media', function () {
        $product = Product::create(['name' => 'Test Product']);

        $result = $product->getMediaManagerMedia();

        expect($result)->toHaveCount(0);
    });

    it('can get media by uuids', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $result = $product->getMediaManagerMediaByUuids([$media1->uuid, $media2->uuid]);

        expect($result)->toHaveCount(2);
        expect($result->pluck('uuid')->toArray())->toContain($media1->uuid, $media2->uuid);
    });

    it('can get media manager input media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $product->addMedia(UploadedFile::fake()->image('test1.jpg'))->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('test2.jpg'))->toMediaCollection('images');

        $result = $product->getMediaManagerInputMedia('images');

        expect($result)->toHaveCount(2);
    });

    it('can attach media manager media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);

        $count = DB::table('media_has_models')
            ->where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->count();

        expect($count)->toBe(2);
    });

    it('prevents duplicate attachments', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media->uuid]);
        $product->attachMediaManagerMedia([$media->uuid]); // Try to attach again

        $count = DB::table('media_has_models')
            ->where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->count();

        expect($count)->toBe(1); // Should still be 1
    });

    it('can detach specific media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);
        $product->detachMediaManagerMedia([$media1->uuid]);

        $count = DB::table('media_has_models')
            ->where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->count();

        expect($count)->toBe(1);
    });

    it('can detach all media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);
        $product->detachMediaManagerMedia(); // Detach all

        $count = DB::table('media_has_models')
            ->where('model_type', Product::class)
            ->where('model_id', $product->id)
            ->count();

        expect($count)->toBe(0);
    });

    it('can sync media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');
        $media3 = $product->addMedia(UploadedFile::fake()->image('test3.jpg'))
            ->toMediaCollection('images');

        // Initially attach media1 and media2
        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);

        // Sync to only media3 (should detach media1 and media2, attach media3)
        $product->syncMediaManagerMedia([$media3->uuid]);

        $attachedUuids = DB::table('media_has_models')
            ->where('media_has_models.model_type', Product::class)
            ->where('media_has_models.model_id', $product->id)
            ->join('media', 'media_has_models.media_id', '=', 'media.id')
            ->pluck('media.uuid')
            ->toArray();

        expect($attachedUuids)->toHaveCount(1);
        expect($attachedUuids)->toContain($media3->uuid);
        expect($attachedUuids)->not->toContain($media1->uuid, $media2->uuid);
    });

    it('can check if media exists', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media->uuid]);

        expect($product->hasMediaManagerMedia($media->uuid))->toBeTrue();
        expect($product->hasMediaManagerMedia('non-existent-uuid'))->toBeFalse();
    });

    it('can get first media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);

        $first = $product->getFirstMediaManagerMedia();

        expect($first)->not->toBeNull();
        expect($first->uuid)->toBeIn([$media1->uuid, $media2->uuid]);
    });

    it('returns null when getting first media on empty collection', function () {
        $product = Product::create(['name' => 'Test Product']);

        $first = $product->getFirstMediaManagerMedia();

        expect($first)->toBeNull();
    });

    it('can get media url', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media->uuid]);

        $url = $product->getMediaManagerUrl();

        expect($url)->not->toBeNull();
        expect($url)->toContain('test.jpg');
    });

    it('returns null when getting url with no media', function () {
        $product = Product::create(['name' => 'Test Product']);

        $url = $product->getMediaManagerUrl();

        expect($url)->toBeNull();
    });

    it('can get all media urls', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product->attachMediaManagerMedia([$media1->uuid, $media2->uuid]);

        $urls = $product->getMediaManagerUrls();

        expect($urls)->toHaveCount(2);
        expect($urls[0])->toContain('test');
        expect($urls[1])->toContain('test');
    });

    it('returns empty array when getting urls with no media', function () {
        $product = Product::create(['name' => 'Test Product']);

        $urls = $product->getMediaManagerUrls();

        expect($urls)->toHaveCount(0);
    });

    it('can filter media by field name', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->withCustomProperties(['field_name' => 'avatar'])
            ->toMediaCollection('images');

        DB::table('media_has_models')->insert([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'media_id' => $media->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $product->getMediaManagerMedia('avatar');

        expect($result)->toHaveCount(1);
        expect($result->first()->getCustomProperty('field_name'))->toBe('avatar');
    });
});

describe('InteractsWithMediaManager with Multiple Models', function () {
    it('isolates media between different models', function () {
        $product1 = Product::create(['name' => 'Product 1']);
        $product2 = Product::create(['name' => 'Product 2']);

        $media1 = $product1->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product2->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        $product1->attachMediaManagerMedia([$media1->uuid]);
        $product2->attachMediaManagerMedia([$media2->uuid]);

        $product1Media = $product1->getMediaManagerMedia();
        $product2Media = $product2->getMediaManagerMedia();

        expect($product1Media)->toHaveCount(1);
        expect($product2Media)->toHaveCount(1);
        expect($product1Media->first()->uuid)->toBe($media1->uuid);
        expect($product2Media->first()->uuid)->toBe($media2->uuid);
    });
});
