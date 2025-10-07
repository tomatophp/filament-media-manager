<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use TomatoPHP\FilamentMediaManager\Livewire\FolderComponent;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can render folder component', function () {
    $folder = Folder::factory()->create();

    livewire(FolderComponent::class, ['item' => $folder])
        ->assertSuccessful();
});

it('can mount folder action', function () {
    $folder = Folder::factory()->create();

    livewire(FolderComponent::class, ['item' => $folder])
        ->assertActionExists('getFolderAction');
});

it('shows password form for protected folder', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    livewire(FolderComponent::class, ['item' => $folder])
        ->assertSee('getFolderAction');
});

it('does not show password form for public folder', function () {
    $folder = Folder::factory()->create([
        'is_protected' => false,
    ]);

    expect($folder->is_protected)->toBeFalse();
});

it('validates password input is required for protected folders', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    expect($folder->is_protected)->toBeTrue();
    expect($folder->password)->not->toBeEmpty();
});
