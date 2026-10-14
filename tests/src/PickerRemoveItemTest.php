<?php

use Filament\Actions\Testing\TestAction;
use TomatoPHP\FilamentMediaManager\Tests\Support\PickerForm;

use function Pest\Livewire\livewire;

it('removes the only item from a multiple picker', function () {
    livewire(PickerForm::class, ['single' => false, 'initial' => ['uuid-1']])
        ->mountAction(TestAction::make('removeMediaItem')->schemaComponent('media'), ['uuid' => 'uuid-1'])->callMountedAction()
        ->assertSet('data.media', []);
});

it('removes the only item from a single picker', function () {
    livewire(PickerForm::class, ['single' => true, 'initial' => 'uuid-1'])
        ->mountAction(TestAction::make('removeMediaItem')->schemaComponent('media'), ['uuid' => 'uuid-1'])->callMountedAction()
        ->assertSet('data.media', null);
});

it('removes the only item from a single picker when the state is a one item array', function () {
    livewire(PickerForm::class, ['single' => true, 'initial' => ['uuid-1']])
        ->mountAction(TestAction::make('removeMediaItem')->schemaComponent('media'), ['uuid' => 'uuid-1'])->callMountedAction()
        ->assertSet('data.media', null);
});
