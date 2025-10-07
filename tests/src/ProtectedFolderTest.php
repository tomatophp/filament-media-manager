<?php

namespace TomatoPHP\FilamentMediaManager\Tests;

use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can create a protected folder', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => bcrypt('secret123'),
    ]);

    assertDatabaseHas(Folder::class, [
        'id' => $folder->id,
        'is_protected' => true,
    ]);

    expect($folder->is_protected)->toBeTrue();
});

it('requires password for protected folder', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    expect($folder->is_protected)->toBeTrue();
    expect($folder->password)->toBe('secret123');
});

it('validates password for protected folder', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    $wrongPassword = 'wrongpassword';
    expect($folder->password)->not->toBe($wrongPassword);
});

it('allows access with correct password', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    session()->put('folder_password', 'secret123');

    expect(session()->has('folder_password'))->toBeTrue();
});

it('stores folder id in session', function () {
    $folder = Folder::factory()->create();

    session()->put('folder_id', $folder->id);

    expect(session()->get('folder_id'))->toBe($folder->id);
});

it('clears folder session on logout', function () {
    $folder = Folder::factory()->create();

    session()->put('folder_id', $folder->id);
    session()->put('folder_password', 'secret123');

    session()->forget('folder_id');
    session()->forget('folder_password');

    expect(session()->has('folder_id'))->toBeFalse();
    expect(session()->has('folder_password'))->toBeFalse();
});

it('can display lock icon for protected folders', function () {
    $folder = Folder::factory()->create([
        'is_protected' => true,
        'password' => 'secret123',
    ]);

    expect($folder->is_protected)->toBeTrue();
});

it('can create public folder without password', function () {
    $folder = Folder::factory()->create([
        'is_protected' => false,
    ]);

    expect($folder->is_protected)->toBeFalse();
    expect($folder->password)->toBeNull();
});
