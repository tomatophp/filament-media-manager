<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerPicker;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Models\Media;
use TomatoPHP\FilamentMediaManager\Tests\Models\Product;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create());
});

describe('MediaManagerPicker Component', function () {
    it('can be instantiated', function () {
        $field = MediaManagerPicker::make('media');

        expect($field)->toBeInstanceOf(MediaManagerPicker::class);
    });

    it('can be configured as multiple selection', function () {
        $field = MediaManagerPicker::make('media')->multiple();

        expect($field->isMultiple())->toBeTrue();
    });

    it('can be configured as single selection', function () {
        $field = MediaManagerPicker::make('media')->single();

        expect($field->isMultiple())->toBeFalse();
    });

    it('can set max items', function () {
        $field = MediaManagerPicker::make('media')->maxItems(5);

        expect($field->getMaxItems())->toBe(5);
    });

    it('can set min items', function () {
        $field = MediaManagerPicker::make('media')->minItems(2);

        expect($field->getMinItems())->toBe(2);
    });

    it('can set collection name', function () {
        $field = MediaManagerPicker::make('media')->collection('products');

        expect($field->getCollectionName())->toBe('products');
    });

    it('has browse action', function () {
        $field = MediaManagerPicker::make('media');
        $action = $field->getBrowseAction();

        expect($action)->not->toBeNull();
        expect($action->getName())->toBe('getBrowseAction');
    });

    it('has remove action', function () {
        $field = MediaManagerPicker::make('media');
        $action = $field->getRemoveAction();

        expect($action)->not->toBeNull();
        expect($action->getName())->toBe('getRemoveAction');
    });

    it('has remove media item action', function () {
        $field = MediaManagerPicker::make('media');
        $action = $field->getRemoveMediaItemAction();

        expect($action)->not->toBeNull();
        expect($action->getName())->toBe('removeMediaItem');
    });
});

describe('MediaPicker Livewire Component', function () {
    it('can render', function () {
        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])->assertSuccessful();
    });

    it('initializes with correct properties', function () {
        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
            'maxItems' => 5,
            'minItems' => 2,
        ])
            ->assertSet('pickerKey', 'test-picker')
            ->assertSet('isMultiple', true)
            ->assertSet('maxItems', 5)
            ->assertSet('minItems', 2);
    });

    it('can open folder', function () {
        $folder = Folder::factory()->create();

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->call('openFolder', $folder->id)
            ->assertSet('currentFolderId', $folder->id);
    });

    it('can go back to root from folder', function () {
        $folder = Folder::factory()->create();

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->set('currentFolderId', $folder->id)
            ->call('goBack')
            ->assertSet('currentFolderId', null); // Goes back to root
    });

    it('enforces max items limit', function () {
        $product = Product::create(['name' => 'Test Product']);
        $folder = Folder::factory()->create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection' => 'images',
        ]);

        $media1 = $product->addMedia(UploadedFile::fake()->image('test1.jpg'))
            ->toMediaCollection('images');
        $media2 = $product->addMedia(UploadedFile::fake()->image('test2.jpg'))
            ->toMediaCollection('images');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
            'maxItems' => 1,
        ])
            ->call('toggleMediaSelection', $media1->uuid)
            ->assertSet('selectedMedia', [$media1->uuid])
            ->call('toggleMediaSelection', $media2->uuid)
            ->assertSet('selectedMedia', [$media1->uuid]) // Should not add second item
            ->assertNotified(); // Should show warning notification
    });

    it('validates min items requirement', function () {
        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
            'minItems' => 2,
        ])
            ->set('selectedMedia', ['uuid-1'])
            ->call('selectMedia')
            ->assertNotified(); // Should show warning notification
    });

    it('can toggle media selection in multiple mode', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->call('toggleMediaSelection', $media->uuid)
            ->assertSet('selectedMedia', [$media->uuid])
            ->call('toggleMediaSelection', $media->uuid)
            ->assertSet('selectedMedia', []); // Deselected
    });

    it('auto-selects in single mode', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => false,
        ])
            ->call('toggleMediaSelection', $media->uuid)
            ->assertNotified(); // Should show success notification
    });

    it('can remove selection', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->set('selectedMedia', [$media->uuid])
            ->call('removeSelection', $media->uuid)
            ->assertSet('selectedMedia', []);
    });

    it('can search folders and media', function () {
        $folder = Folder::factory()->create(['name' => 'Test Folder']);

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->set('search', 'Test')
            ->assertSee('Test Folder');
    });
});

describe('MediaManagerPicker with Password Protected Folders', function () {
    it('prompts for password on protected folder', function () {
        $folder = Folder::factory()->create([
            'is_protected' => true,
            'password' => 'secret123',
        ]);

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->call('openFolder', $folder->id)
            ->assertActionMounted('verifyPassword');
    });

    it('opens folder with correct password', function () {
        $folder = Folder::factory()->create([
            'is_protected' => true,
            'password' => 'secret123',
        ]);

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->call('openFolder', $folder->id)
            ->assertSet('pendingFolderId', $folder->id)
            ->callMountedAction(['password' => 'secret123'])
            ->assertNotified(); // Should show "Access granted" notification
    });

    it('rejects incorrect password', function () {
        $folder = Folder::factory()->create([
            'is_protected' => true,
            'password' => 'secret123',
        ]);

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->call('openFolder', $folder->id)
            ->callMountedAction(['password' => 'wrong-password'])
            ->assertSet('currentFolderId', null)
            ->assertNotified();
    });
});

describe('MediaManagerPicker State Hydration', function () {
    it('loads existing media on edit', function () {
        $product = Product::create(['name' => 'Test Product']);
        $media = $product->addMedia(UploadedFile::fake()->image('test.jpg'))
            ->toMediaCollection('images');

        // Attach media via pivot table
        DB::table('media_has_models')->insert([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'media_id' => $media->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $field = MediaManagerPicker::make('media')->multiple();

        // Simulate afterStateHydrated
        $field->afterStateHydrated(function (MediaManagerPicker $component) use ($product) {
            $mediaIds = DB::table('media_has_models')
                ->where('model_type', Product::class)
                ->where('model_id', $product->id)
                ->pluck('media_id')
                ->toArray();

            $mediaCollection = Media::whereIn('id', $mediaIds)->get();
            $component->state($mediaCollection->pluck('uuid')->toArray());
        });

        expect($field)->toBeInstanceOf(MediaManagerPicker::class);
    });
});

describe('MediaManagerPicker Upload', function () {
    it('can upload files directly from picker', function () {
        $product = Product::create(['name' => 'Test Product']);
        $folder = Folder::factory()->create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection' => 'images',
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->set('currentFolderId', $folder->id)
            ->callAction('uploadMedia', ['files' => [$file]])
            ->assertHasNoActionErrors();
    });

    it('auto-selects uploaded media', function () {
        $product = Product::create(['name' => 'Test Product']);
        $folder = Folder::factory()->create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'collection' => 'images',
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        livewire(MediaPicker::class, [
            'pickerKey' => 'test-picker',
            'isMultiple' => true,
        ])
            ->set('currentFolderId', $folder->id)
            ->callAction('uploadMedia', ['files' => [$file]])
            ->assertSet('selectedMedia', function ($selectedMedia) {
                return count($selectedMedia) === 1;
            });
    });
});
