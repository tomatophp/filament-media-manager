<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use Filament\Facades\Filament;
use TomatoPHP\FilamentMediaManager\Livewire\FolderComponent;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());

    $this->panel->resources([
        FolderResource::class,
        MediaResource::class,
    ]);

    // The folder card normally runs inside a panel request.
    Filament::setCurrentPanel($this->panel);

    $this->folder = Folder::factory()->protected('top-secret-123')->create();
});

it('does not render a protected folder password into the page', function () {
    livewire(FolderComponent::class, ['item' => $this->folder])
        ->assertSuccessful()
        ->assertDontSee('top-secret-123');
});

it('rejects a password check whose arguments were tampered with', function () {
    livewire(FolderComponent::class, ['item' => $this->folder])
        ->mountAction('getFolderAction', ['item' => [
            'id' => $this->folder->id,
            'is_protected' => true,
            'password' => 'guessed',
            'model_type' => null,
        ]])
        ->fillForm(['password' => 'guessed'])
        ->callMountedAction()
        ->assertNotified('Password is incorrect')
        ->assertNoRedirect();

    expect(session()->has('folder_password'))->toBeFalse();
});

it('opens a protected folder with the right password', function () {
    livewire(FolderComponent::class, ['item' => $this->folder])
        ->mountAction('getFolderAction', ['item' => $this->folder->id])
        ->fillForm(['password' => 'top-secret-123'])
        ->callMountedAction()
        ->assertRedirect(MediaResource::getUrl('index', ['folder_id' => $this->folder->id]));
});
