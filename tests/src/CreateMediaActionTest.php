<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $this->panel->resources([
        MediaResource::class,
    ]);

    Storage::fake('public');

    $this->folder = Folder::factory()->create([
        'collection' => 'images',
        'is_protected' => false,
    ]);
});

it('fills the add media form when it opens', function () {
    $component = Livewire::withQueryParams(['folder_id' => $this->folder->id])
        ->test(ListMedia::class)
        ->mountAction('create_media')
        ->assertActionMounted('create_media');

    expect($component->get('mountedActions.0.data'))
        ->toHaveKeys(['file', 'title', 'description']);
});

it('adds the uploaded file to the folder', function () {
    Livewire::withQueryParams(['folder_id' => $this->folder->id])
        ->test(ListMedia::class)
        ->mountAction('create_media')
        ->setActionData([
            'file' => [UploadedFile::fake()->image('photo.jpg')],
            'title' => 'Photo',
            'description' => 'A photo',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $media = $this->folder->getMedia('images');

    expect($media)->toHaveCount(1)
        ->and($media->first()->file_name)->toBe('photo.jpg')
        ->and($media->first()->getCustomProperty('title'))->toBe('Photo');
});

it('adds several files at once, without a title or description', function () {
    Livewire::withQueryParams(['folder_id' => $this->folder->id])
        ->test(ListMedia::class)
        ->mountAction('create_media')
        ->setActionData([
            'file' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
                UploadedFile::fake()->create('three.pdf', 10, 'application/pdf'),
            ],
            'title' => 'Ignored',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();

    $media = $this->folder->getMedia('images');

    expect($media->pluck('file_name')->sort()->values()->all())->toBe(['one.jpg', 'three.pdf', 'two.jpg'])
        ->and($media->map(fn ($item) => $item->getCustomProperty('title'))->filter()->all())->toBe([]);
});

it('offers a title and a description only for a single file', function () {
    $component = Livewire::withQueryParams(['folder_id' => $this->folder->id])
        ->test(ListMedia::class)
        ->mountAction('create_media')
        ->assertFormFieldVisible('title')
        ->assertFormFieldVisible('description');

    $component
        ->setActionData(['file' => [UploadedFile::fake()->image('one.jpg')]])
        ->assertFormFieldVisible('title')
        ->setActionData(['file' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')]])
        ->assertFormFieldHidden('title')
        ->assertFormFieldHidden('description');
});
