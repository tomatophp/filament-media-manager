<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $this->user = $user;
});

function allowSubFolders(bool $condition = true): void
{
    /** @var FilamentMediaManagerPlugin $plugin */
    $plugin = filament()->getPanel('admin')->getPlugin('filament-media-manager');
    $plugin->allowSubFolders($condition);
}

function picker(array $parameters = [])
{
    return Livewire::test(MediaPicker::class, ['pickerKey' => 'picker', ...$parameters]);
}

it('creates a folder at the top level and opens it', function () {
    $component = picker()
        ->assertActionVisible('createFolder')
        ->callAction('createFolder', data: ['name' => 'Team photos', 'collection' => 'team-photos'])
        ->assertHasNoErrors();

    $folder = Folder::query()->where('collection', 'team-photos')->sole();

    expect($folder)
        ->name->toBe('Team photos')
        ->model_type->toBeNull()
        ->model_id->toBeNull()
        ->user_id->toBe($this->user->id)
        ->and($component->get('currentFolderId'))->toBe($folder->id);
});

it('creates a sub folder in the open folder with sub folders allowed', function () {
    allowSubFolders();
    $parent = Folder::factory()->create();

    $component = picker()
        ->set('currentFolderId', $parent->id)
        ->callAction('createFolder', data: ['name' => 'Events', 'collection' => 'events'])
        ->assertHasNoErrors();

    $folder = Folder::query()->where('collection', 'events')->sole();

    expect($folder)
        ->model_type->toBe(Folder::class)
        ->model_id->toBe($parent->id)
        ->and($component->get('currentFolderId'))->toBe($folder->id);

    // The picker shows the new folder right away, not the parent with the new folder's (empty) contents
    $component->assertSee('Events');

    // It is listed in its parent like any other sub folder
    $component->set('currentFolderId', $parent->id)
        ->assertSee('Events');
});

it('offers no sub folder without allowSubFolders()', function () {
    allowSubFolders(false);
    $parent = Folder::factory()->create();

    picker()
        ->set('currentFolderId', $parent->id)
        ->assertActionHidden('createFolder');
});

it('offers no folder inside the folders of a model', function () {
    allowSubFolders();
    $modelFolder = Folder::factory()->create(['model_type' => User::class, 'model_id' => null, 'collection' => null]);

    picker()
        ->set('currentFolderId', $modelFolder->id)
        ->assertActionHidden('createFolder');
});

it('offers no folder in a picker limited to a collection', function () {
    picker(['collectionName' => 'avatars'])
        ->assertActionHidden('createFolder');
});

it('stays at the current level after creating a protected folder', function () {
    picker()
        ->callAction('createFolder', data: [
            'name' => 'Private',
            'collection' => 'private',
            'is_protected' => true,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])
        ->assertHasNoErrors()
        ->assertSet('currentFolderId', null);

    expect(Folder::query()->where('collection', 'private')->sole()->is_protected)->toBeTrue();
});

it('requires a unique collection, like the other folder forms', function () {
    Folder::factory()->create(['collection' => 'taken']);

    picker()
        ->callAction('createFolder', data: ['name' => 'Taken', 'collection' => 'taken'])
        ->assertHasActionErrors(['collection' => 'unique']);
});

it('still creates a sub folder in the media manager with the shared form', function () {
    allowSubFolders();
    $parent = Folder::factory()->create(['is_protected' => false]);

    $this->panel->resources([MediaResource::class]);

    Livewire::withQueryParams(['folder_id' => $parent->id])
        ->test(ListMedia::class)
        ->callAction('create_sub_folder', data: ['name' => 'Archive', 'collection' => 'archive'])
        ->assertHasNoActionErrors();

    expect(Folder::query()->where('collection', 'archive')->sole())
        ->model_type->toBe(Folder::class)
        ->model_id->toBe($parent->id);
});
