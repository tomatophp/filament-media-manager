<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Filament\Actions\EditAction;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource\Pages;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());

    $this->panel->resources([
        FolderResource::class,
    ]);
});

it('can render folder resource', function () {
    get(FolderResource::getUrl())->assertSuccessful();
});

it('can list folders', function () {
    Folder::factory()->count(10)->create();

    livewire(Pages\ListFolders::class)
        ->assertSuccessful();
});

it('can render folder name/collection columns in table', function () {
    Folder::factory()->count(10)->create();

    livewire(Pages\ListFolders::class)
        ->assertSuccessful();
});

it('can render folder list page', function () {
    livewire(Pages\ListFolders::class)->assertSuccessful();
});

it('can render folder create action', function () {
    livewire(Pages\ListFolders::class)
        ->callAction('create')
        ->assertSuccessful();
});

it('can create new folder', function () {
    livewire(Pages\ListFolders::class)
        ->callAction('create', data: [
            'name' => 'Test Folder',
            'collection' => 'test-folder',
            'description' => 'Test Description',
            'icon' => 'heroicon-o-folder',
            'color' => '#f3c623',
            'is_protected' => false,
        ])
        ->assertHasNoActionErrors();

    assertDatabaseHas(Folder::class, [
        'name' => 'Test Folder',
        'collection' => 'test-folder',
    ]);
});

it('can create protected folder with password', function () {
    livewire(Pages\ListFolders::class)
        ->callAction('create', data: [
            'name' => 'Protected Folder',
            'collection' => 'protected-folder',
            'description' => 'Protected Description',
            'icon' => 'heroicon-o-folder',
            'color' => '#f3c623',
            'is_protected' => true,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertHasNoActionErrors();

    assertDatabaseHas(Folder::class, [
        'name' => 'Protected Folder',
        'is_protected' => true,
    ]);
});

it('can validate folder input', function () {
    livewire(Pages\ListFolders::class)
        ->callAction('create', data: [
            'name' => null,
            'collection' => null,
        ])
        ->assertHasActionErrors([
            'name' => 'required',
            'collection' => 'required',
        ]);
});

it('can render folder edit action', function () {
    $folder = Folder::factory()->create();

    livewire(Pages\ListFolders::class)
        ->mountTableAction('edit', $folder)
        ->assertSuccessful();
});

it('can retrieve folder data', function () {
    $folder = Folder::factory()->create();

    livewire(Pages\ListFolders::class)
        ->mountTableAction(EditAction::class, $folder)
        ->assertTableActionDataSet([
            'name' => $folder->name,
            'collection' => $folder->collection,
        ])
        ->assertHasNoTableActionErrors();
});

it('can validate edit folder input', function () {
    $folder = Folder::factory()->create();

    livewire(Pages\ListFolders::class)
        ->callTableAction('edit', $folder, [
            'name' => null,
            'collection' => null,
        ])
        ->assertHasTableActionErrors([
            'name' => 'required',
            'collection' => 'required',
        ]);
});

it('can save folder data', function () {
    $folder = Folder::factory()->create();

    livewire(Pages\ListFolders::class)
        ->callTableAction('edit', $folder, data: [
            'name' => 'Updated Folder',
            'collection' => 'updated-folder',
            'description' => 'Updated Description',
        ])
        ->assertHasNoTableActionErrors();

    expect($folder->refresh())
        ->name->toBe('Updated Folder')
        ->collection->toBe('updated-folder');
});

it('can delete folder', function () {
    $folder = Folder::factory()->create();

    livewire(Pages\ListFolders::class)
        ->callTableAction('delete', $folder)
        ->assertHasNoTableActionErrors();

    assertModelMissing($folder);
});

it('can filter folders by protected status', function () {
    Folder::factory()->count(5)->create(['is_protected' => true]);
    Folder::factory()->count(5)->create(['is_protected' => false]);

    livewire(Pages\ListFolders::class)
        ->assertSuccessful();
});
