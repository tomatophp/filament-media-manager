<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $this->panel->resources([
        FolderResource::class,
        MediaResource::class,
    ]);
});

function folderBreadcrumbs(Folder $folder): array
{
    /** @var ListMedia $page */
    $page = Livewire::withQueryParams(['folder_id' => $folder->id])
        ->test(ListMedia::class)
        ->instance();

    return $page->getBreadcrumbs();
}

function subFolder(Folder $parent, string $name): Folder
{
    return Folder::factory()->create([
        'name' => $name,
        'model_type' => Folder::class,
        'model_id' => $parent->id,
    ]);
}

it('leads from the folders list to a folder', function () {
    $folder = Folder::factory()->create(['name' => 'Photos']);

    expect(folderBreadcrumbs($folder))->toBe([
        FolderResource::getUrl('index') => FolderResource::getNavigationLabel(),
        0 => 'Photos',
    ]);
});

it('links every parent folder of a sub folder', function () {
    $photos = Folder::factory()->create(['name' => 'Photos']);
    $events = subFolder($photos, 'Events');
    $summer = subFolder($events, 'Summer party');

    expect(folderBreadcrumbs($summer))->toBe([
        FolderResource::getUrl('index') => FolderResource::getNavigationLabel(),
        MediaResource::getUrl('index', ['folder_id' => $photos->id]) => 'Photos',
        MediaResource::getUrl('index', ['folder_id' => $events->id]) => 'Events',
        0 => 'Summer party',
    ]);
});

it('leads to the folder of a record through its model type and collection', function () {
    Folder::factory()->create(['name' => 'Users', 'model_type' => User::class, 'model_id' => null, 'collection' => null]);
    Folder::factory()->create(['name' => 'Avatars', 'model_type' => User::class, 'model_id' => null, 'collection' => 'avatars']);
    $record = Folder::factory()->create(['name' => 'Jane', 'model_type' => User::class, 'model_id' => 1, 'collection' => 'avatars']);

    expect(folderBreadcrumbs($record))->toBe([
        FolderResource::getUrl('index') => FolderResource::getNavigationLabel(),
        FolderResource::getUrl('index', ['model_type' => User::class]) => 'Users',
        FolderResource::getUrl('index', ['model_type' => User::class, 'collection' => 'avatars']) => 'Avatars',
        0 => 'Jane',
    ]);
});

it('names the model type and collection levels without folders for them', function () {
    $record = Folder::factory()->create(['name' => 'Jane', 'model_type' => User::class, 'model_id' => 1, 'collection' => 'profile-images']);

    expect(array_values(folderBreadcrumbs($record)))->toBe([
        FolderResource::getNavigationLabel(),
        'User',
        'Profile Images',
        'Jane',
    ]);
});

it('stops at a parent folder that is gone or loops back', function () {
    $orphan = Folder::factory()->create(['name' => 'Orphan', 'model_type' => Folder::class, 'model_id' => 999]);

    $first = Folder::factory()->create(['name' => 'First']);
    $second = subFolder($first, 'Second');
    $first->update(['model_type' => Folder::class, 'model_id' => $second->id]);

    expect(array_values(folderBreadcrumbs($orphan)))->toBe([FolderResource::getNavigationLabel(), 'Orphan'])
        ->and(array_values(folderBreadcrumbs($second)))->toBe([FolderResource::getNavigationLabel(), 'First', 'Second']);
});

it('shows the trail on the folders page of a model type and collection', function () {
    Folder::factory()->create(['name' => 'Users', 'model_type' => User::class, 'model_id' => null, 'collection' => null]);

    get(FolderResource::getUrl('index', ['model_type' => User::class, 'collection' => 'avatars']))
        ->assertSuccessful()
        ->assertSeeInOrder([FolderResource::getNavigationLabel(), 'Users', 'Avatars'])
        ->assertSee(FolderResource::getUrl('index', ['model_type' => User::class]), escape: false);
});

it('shows the trail on the media page', function () {
    $photos = Folder::factory()->create(['name' => 'Photos']);
    $events = subFolder($photos, 'Events');

    get(MediaResource::getUrl('index', ['folder_id' => $events->id]))
        ->assertSuccessful()
        ->assertSee(MediaResource::getUrl('index', ['folder_id' => $photos->id]), escape: false);
});

it('shows the parent folders in the media picker and opens them', function () {
    $top = Folder::factory()->create(['name' => 'Photos']);
    $events = subFolder($top, 'Events');
    $summer = subFolder($events, 'Summer');

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $summer->id)
        ->assertSet('breadcrumbs', [
            ['id' => $top->id, 'label' => 'Photos'],
            ['id' => $events->id, 'label' => 'Events'],
            ['id' => $summer->id, 'label' => 'Summer'],
        ])
        ->assertSeeInOrder([FolderResource::getNavigationLabel(), 'Photos', 'Events', 'Summer'])
        ->assertSeeHtml('wire:click="openFolder(' . $top->id . ')"')
        ->assertSeeHtml('wire:click="openFolder(' . $events->id . ')"')
        ->assertDontSeeHtml('wire:click="openFolder(' . $summer->id . ')"')
        ->call('openFolder', $top->id)
        ->assertSet('currentFolderId', $top->id)
        ->assertSet('breadcrumbs', [['id' => $top->id, 'label' => 'Photos']])
        ->set('currentFolderId', null)
        ->assertSet('breadcrumbs', []);
});

it('shows the model type and collection levels in the media picker, openable only with a folder', function () {
    $type = Folder::factory()->create(['name' => 'Users', 'model_type' => User::class, 'model_id' => null, 'collection' => null]);
    $record = Folder::factory()->create(['name' => 'Jane', 'model_type' => User::class, 'model_id' => 1, 'collection' => 'avatars']);

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $record->id)
        ->assertSet('breadcrumbs', [
            ['id' => $type->id, 'label' => 'Users'],
            ['id' => null, 'label' => 'Avatars'],
            ['id' => $record->id, 'label' => 'Jane'],
        ])
        ->assertSeeHtml('wire:click="openFolder(' . $type->id . ')"')
        ->assertSeeHtml('<span class="media-picker-breadcrumb-text" title="Avatars">Avatars</span>');
});
