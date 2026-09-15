<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    actingAs(User::factory()->create());

    $this->panel->resources([
        FolderResource::class,
        MediaResource::class,
    ]);
});

// Opening the media page without a folder (bookmark, navigation) sends the user to the folders list.
it('redirects the media page without a folder to the folders list', function () {
    get(MediaResource::getUrl('index'))
        ->assertRedirect(FolderResource::getUrl('index'));
});

it('still rejects an unknown folder', function () {
    get(MediaResource::getUrl('index') . '?folder_id=999999')->assertNotFound();
});

it('renders the media page of an existing folder', function () {
    $folder = Folder::factory()->create();

    get(MediaResource::getUrl('index') . '?folder_id=' . $folder->id)->assertSuccessful();
});
