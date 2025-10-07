<?php

namespace TomatoPHP\FilamentMediaManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;

class FolderComponent extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public $item;

    public function mount($item): void
    {
        $this->item = $item;
    }

    public function getFolderAction(): Action
    {
        return Action::make('getFolderAction')
            ->color('danger')
            ->view('filament-media-manager::components.folder-action-view', fn (array $arguments) => ['item' => $arguments['item']])
            ->requiresConfirmation(function (array $arguments) {
                if (isset($arguments['item'])) {
                    if ($arguments['item']['is_protected']) {
                        return true;
                    } else {
                        return false;
                    }
                }
            })
            ->schema(function (array $arguments) {
                if (isset($arguments['item'])) {
                    if ($arguments['item']['is_protected']) {
                        return [
                            TextInput::make('password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->maxLength(255),
                        ];
                    } else {
                        return null;
                    }
                }
            })
            ->action(function (array $arguments, array $data) {
                if (isset($arguments['item'])) {
                    if ($arguments['item']['is_protected']) {
                        if ($arguments['item']['password'] != $data['password']) {
                            Notification::make()
                                ->title('Password is incorrect')
                                ->danger()
                                ->send();

                            return;
                        } else {
                            session()->put('folder_password', $data['password']);
                        }
                    }
                    if (! $arguments['item']['model_type']) {
                        if (filament()->getTenant()) {
                            return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/media?folder_id=' . $arguments['item']['id']));
                        } else {
                            return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.media.index', ['folder_id' => $arguments['item']['id']]);
                        }
                    }
                    if (! $arguments['item']['model_id'] && ! $arguments['item']['collection']) {
                        if (filament()->getTenant()) {
                            return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/folders?model_type=' . $arguments['item']['model_type']));
                        } else {
                            return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.folders.index', ['model_type' => $arguments['item']['model_type']]);
                        }
                    } elseif (! $arguments['item']['model_id']) {
                        if (filament()->getTenant()) {
                            return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/folders?model_type=' . $arguments['item']['model_type'] . '&collection=' . $arguments['item']['collection']));
                        } else {
                            return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.folders.index', ['model_type' => $arguments['item']['model_type'], 'collection' => $arguments['item']['collection']]);
                        }
                    } else {
                        if (filament()->getTenant()) {
                            return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/media?folder_id=' . $arguments['item']['id']));
                        } else {
                            return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.media.index', ['folder_id' => $arguments['item']['id']]);
                        }
                    }
                }
            });
    }

    public function render(): mixed
    {
        return view('filament-media-manager::livewire.folder-component');
    }
}
