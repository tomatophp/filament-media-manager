<?php

namespace TomatoPHP\FilamentMediaManager\Tests\Support;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerPicker;

class PickerForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public bool $single = false;

    public function mount(bool $single = false, mixed $initial = null): void
    {
        $this->single = $single;
        $this->form->fill(['media' => $initial]);
    }

    public function form(Schema $schema): Schema
    {
        $picker = MediaManagerPicker::make('media');
        $picker = $this->single ? $picker->single() : $picker->multiple();

        return $schema->components([$picker])->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}<x-filament-actions::modals /></div>';
    }
}
