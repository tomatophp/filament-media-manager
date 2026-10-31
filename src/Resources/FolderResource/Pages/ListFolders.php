<?php

namespace TomatoPHP\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Services\FolderBreadcrumbs;

class ListFolders extends ManageRecords
{
    protected static string $resource = FolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        if (! request()->has('model_type')) {
            return parent::getBreadcrumbs();
        }

        $breadcrumbs = FolderBreadcrumbs::forList(request()->input('model_type'), request()->input('collection'));

        // The current level is the page itself, so it is not a link
        $current = array_pop($breadcrumbs);
        $breadcrumbs[] = $current;

        return $breadcrumbs;
    }

    public function mount(): void
    {
        parent::mount();

        session()->forget('folder_id');
        session()->forget('folder_password');
    }
}
