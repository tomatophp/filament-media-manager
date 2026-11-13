<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Tests\Models\Product;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);
});

function pickerSubFolder(Folder $parent, string $collection): Folder
{
    return Folder::factory()->create([
        'collection' => $collection,
        'model_type' => Folder::class,
        'model_id' => $parent->id,
    ]);
}

it('goes back from a sub folder to its parent, one level at a time', function () {
    $top = Folder::factory()->create(['collection' => 'pages']);
    $sub = pickerSubFolder($top, 'events');
    $subSub = pickerSubFolder($sub, 'summer');

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $subSub->id)
        ->call('goBack')
        ->assertSet('currentFolderId', $sub->id)
        ->call('goBack')
        ->assertSet('currentFolderId', $top->id)
        ->call('goBack')
        ->assertSet('currentFolderId', null);
});

it('still goes back from a record folder to its collection and model type folders', function () {
    $product = Product::create(['name' => 'Chair']);
    $typeFolder = Folder::factory()->create(['model_type' => Product::class, 'collection' => null]);
    $collectionFolder = Folder::factory()->create(['model_type' => Product::class, 'collection' => 'images']);
    $recordFolder = Folder::factory()->create([
        'model_type' => Product::class,
        'model_id' => $product->id,
        'collection' => 'images',
    ]);

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $recordFolder->id)
        ->call('goBack')
        ->assertSet('currentFolderId', $collectionFolder->id)
        ->call('goBack')
        ->assertSet('currentFolderId', $typeFolder->id);
});

it('asks for the password when going back into a protected parent', function () {
    $parent = Folder::factory()->protected('top-secret-123')->create(['collection' => 'private']);
    $child = pickerSubFolder($parent, 'public');

    Livewire::test(MediaPicker::class, ['pickerKey' => 'picker'])
        ->set('currentFolderId', $child->id)
        ->call('goBack')
        ->assertSet('currentFolderId', $child->id)
        ->assertSet('pendingFolderId', $parent->id)
        ->assertActionMounted('verifyPassword')
        ->setActionData(['password' => 'top-secret-123'])
        ->callMountedAction()
        ->assertSet('currentFolderId', $parent->id);
});
