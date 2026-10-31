<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $this->user = $user;
});

function allowUserAccess(bool $condition = true): void
{
    /** @var FilamentMediaManagerPlugin $plugin */
    $plugin = filament()->getPanel('admin')->getPlugin('filament-media-manager');
    $plugin->allowUserAccess($condition);
}

it('renames the open folder and shows the new name right away', function () {
    $folder = Folder::factory()->create(['name' => 'Photos', 'collection' => 'photos']);

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $folder->id)
        ->assertActionVisible('editFolder')
        ->mountAction('editFolder')
        ->assertActionDataSet(['name' => 'Photos'])
        ->setActionData(['name' => 'Archive'])
        ->callMountedAction()
        ->assertHasNoErrors()
        ->assertSet('currentFolderId', $folder->id)
        ->assertSee('Archive');

    expect($folder->refresh())
        ->name->toBe('Archive')
        ->collection->toBe('photos');
});

it('offers no folder to edit at the top level', function () {
    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->assertActionHidden('editFolder');
});

it('lets only the owner edit a folder with user access', function () {
    allowUserAccess();

    $own = Folder::factory()->create(['user_id' => $this->user->id, 'user_type' => User::class]);
    $other = Folder::factory()->create(['user_id' => User::factory()->create()->id, 'user_type' => User::class]);

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $own->id)
        ->assertActionVisible('editFolder')
        ->set('currentFolderId', $other->id)
        ->assertActionHidden('editFolder');

    allowUserAccess(false);
});
