<?php

namespace TomatoPHP\FilamentMediaManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
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
            ->view('filament-media-manager::components.folder-action-view', fn (array $arguments) => ['item' => $this->resolveFolder($arguments)])
            ->requiresConfirmation(fn (array $arguments): bool => (bool) $this->resolveFolder($arguments)?->is_protected)
            ->schema(function (array $arguments) {
                if (! $this->resolveFolder($arguments)?->is_protected) {
                    return null;
                }

                return [
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->maxLength(255),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $folder = $this->resolveFolder($arguments);

                if (! $folder) {
                    return;
                }

                if ($folder->is_protected) {
                    // Check against the stored password, never against a value sent by the browser.
                    if (! hash_equals((string) $folder->password, (string) ($data['password'] ?? ''))) {
                        Notification::make()
                            ->title('Password is incorrect')
                            ->danger()
                            ->send();

                        return;
                    }

                    session()->put('folder_password', $data['password']);
                }

                if (! $folder->model_type) {
                    if (filament()->getTenant()) {
                        return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/media?folder_id=' . $folder->id));
                    } else {
                        return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.media.index', ['folder_id' => $folder->id]);
                    }
                }
                if (! $folder->model_id && ! $folder->collection) {
                    if (filament()->getTenant()) {
                        return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/folders?model_type=' . $folder->model_type));
                    } else {
                        return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.folders.index', ['model_type' => $folder->model_type]);
                    }
                } elseif (! $folder->model_id) {
                    if (filament()->getTenant()) {
                        return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/folders?model_type=' . $folder->model_type . '&collection=' . $folder->collection));
                    } else {
                        return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.folders.index', ['model_type' => $folder->model_type, 'collection' => $folder->collection]);
                    }
                } else {
                    if (filament()->getTenant()) {
                        return redirect()->to(url(filament()->getCurrentPanel()->getId() . '/' . filament()->getTenant()->id . '/media?folder_id=' . $folder->id));
                    } else {
                        return redirect()->route('filament.' . filament()->getCurrentPanel()->getId() . '.resources.media.index', ['folder_id' => $folder->id]);
                    }
                }
            });
    }

    /**
     * Load the folder from the database; the browser only sends its id.
     */
    protected function resolveFolder(array $arguments): ?Model
    {
        $item = $arguments['item'] ?? null;

        if ($item instanceof Model) {
            return $item;
        }

        $id = is_array($item) ? ($item['id'] ?? null) : $item;

        return filled($id) ? config('filament-media-manager.model.folder')::find($id) : null;
    }

    public function render(): mixed
    {
        return view('filament-media-manager::livewire.folder-component');
    }
}
