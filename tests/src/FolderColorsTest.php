<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;
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

function mediaManagerPlugin(): FilamentMediaManagerPlugin
{
    /** @var FilamentMediaManagerPlugin $plugin */
    $plugin = filament()->getPanel('admin')->getPlugin('filament-media-manager');

    return $plugin;
}

describe('default color', function () {
    it('shows folders without a color in the package color by default', function () {
        expect(Folder::factory()->create(['color' => ''])->getDisplayColor())->toBe('#f3c623')
            ->and(Folder::factory()->create(['color' => null])->getDisplayColor())->toBe('#f3c623');
    });

    it('shows folders without a color in the configured default', function () {
        mediaManagerPlugin()->defaultFolderColor('#123456');

        expect(Folder::factory()->create(['color' => ''])->getDisplayColor())->toBe('#123456')
            ->and(Folder::factory()->create(['color' => '#abcdef'])->getDisplayColor())->toBe('#abcdef');
    });

    it('renders the default for a folder without a color', function () {
        mediaManagerPlugin()->defaultFolderColor('#123456');
        Folder::factory()->create(['color' => '']);

        get(FolderResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('background-color: #123456', escape: false);
    });

    it('starts new folders with the default color', function () {
        mediaManagerPlugin()->defaultFolderColor('#123456')->allowSubFolders();
        $parent = Folder::factory()->create(['is_protected' => false]);

        Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
            ->mountAction('createFolder')
            ->assertActionDataSet(['color' => '#123456']);

        Livewire::withQueryParams(['folder_id' => $parent->id])
            ->test(ListMedia::class)
            ->mountAction('create_sub_folder')
            ->assertActionDataSet(['color' => '#123456']);
    });
});

describe('palette', function () {
    it('offers no palette without folderColors()', function () {
        Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
            ->mountAction('createFolder')
            ->assertFormFieldHidden('color_palette');
    });

    it('sets the color from the palette and saves it', function () {
        mediaManagerPlugin()->folderColors(['#ff0000', '#00ff00']);

        Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
            ->mountAction('createFolder')
            ->assertFormFieldVisible('color_palette')
            ->setActionData(['color_palette' => '#00ff00'])
            ->assertActionDataSet(['color' => '#00ff00'])
            ->setActionData(['name' => 'Green', 'collection' => 'green'])
            ->callMountedAction()
            ->assertHasNoErrors();

        expect(Folder::query()->where('collection', 'green')->sole()->color)->toBe('#00ff00');
    });

    it('marks the swatch of a palette color and none for another', function () {
        mediaManagerPlugin()->folderColors(['#ff0000', '#00ff00']);
        $green = Folder::factory()->create(['color' => '#00ff00', 'is_protected' => false]);
        $blue = Folder::factory()->create(['color' => '#0000ff', 'is_protected' => false]);

        Livewire::withQueryParams(['folder_id' => $green->id])
            ->test(ListMedia::class)
            ->mountAction('edit_current_folder')
            ->assertActionDataSet(['color_palette' => '#00ff00', 'color' => '#00ff00']);

        Livewire::withQueryParams(['folder_id' => $blue->id])
            ->test(ListMedia::class)
            ->mountAction('edit_current_folder')
            ->assertActionDataSet(['color_palette' => null, 'color' => '#0000ff']);
    });
});
