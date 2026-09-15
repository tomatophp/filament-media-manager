<?php

use TomatoPHP\FilamentMediaManager\Resources\Actions\EditCurrentFolderAction;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    config()->set('filament-media-manager.user.model', User::class);
    config()->set('filament-media-manager.user.column_name', 'name');
});

it('keys the share users by the id column by default', function () {
    $me = User::factory()->create(['name' => 'Me']);
    $other = User::factory()->create(['name' => 'Other']);

    actingAs($me);

    expect(EditCurrentFolderAction::userOptions())->toBe([$other->id => 'Other']);
});

// PR #55: apps whose users are keyed by another column can configure it.
it('keys the share users by the configured id column', function () {
    config()->set('filament-media-manager.user.id_column', 'email');

    $me = User::factory()->create(['name' => 'Me', 'email' => 'me@example.com']);
    User::factory()->create(['name' => 'Ann', 'email' => 'ann@example.com']);

    actingAs($me);

    expect(EditCurrentFolderAction::userOptions())->toBe(['ann@example.com' => 'Ann']);
});
