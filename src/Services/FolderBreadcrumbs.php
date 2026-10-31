<?php

namespace TomatoPHP\FilamentMediaManager\Services;

use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;

/**
 * The trail from the folders list down to a folder, for the page breadcrumbs.
 *
 * Folders nest in two ways: a sub folder points to its parent through model_type = Folder and model_id, and a
 * folder of a model record (model_type and model_id set) sits in a collection folder (model_type and collection
 * set), which sits in the folder of its model type (only model_type set). Those two levels are listed on the
 * folders page, filtered by model_type and collection, like a click on them does.
 */
class FolderBreadcrumbs
{
    /**
     * @return array<string, string> label by url, the folder itself last
     */
    public static function forFolder(Folder $folder): array
    {
        $trail = [];
        $visited = [];
        $current = $folder;

        while ($current && ! in_array($current->getKey(), $visited, true)) {
            $visited[] = $current->getKey();

            $trail = [MediaResource::getUrl('index', ['folder_id' => $current->getKey()]) => $current->name] + $trail;

            if ($current->model_type === Folder::class && $current->model_id) {
                // A folder that is not accessible (FolderAccess scope) ends the trail
                $current = Folder::query()->find($current->model_id);

                continue;
            }

            if ($current->model_type && $current->model_id) {
                $trail = static::forModelType($current->model_type, $current->collection) + $trail;
            }

            break;
        }

        return static::root() + $trail;
    }

    /**
     * The folders page, filtered to a model type and a collection
     *
     * @return array<string, string>
     */
    public static function forList(?string $modelType, ?string $collection): array
    {
        return static::root() + ($modelType ? static::forModelType($modelType, $collection) : []);
    }

    /**
     * @return array<string, string>
     */
    protected static function forModelType(string $modelType, ?string $collection): array
    {
        $typeFolder = Folder::query()
            ->where('model_type', $modelType)
            ->whereNull('model_id')
            ->whereNull('collection')
            ->first();

        $trail = [
            FolderResource::getUrl('index', ['model_type' => $modelType]) => $typeFolder->name
                ?? (string) str($modelType)->afterLast('\\')->headline(),
        ];

        if (filled($collection)) {
            $collectionFolder = Folder::query()
                ->where('model_type', $modelType)
                ->whereNull('model_id')
                ->where('collection', $collection)
                ->first();

            $trail[FolderResource::getUrl('index', ['model_type' => $modelType, 'collection' => $collection])] = $collectionFolder->name
                ?? (string) str($collection)->headline();
        }

        return $trail;
    }

    /**
     * @return array<string, string>
     */
    protected static function root(): array
    {
        return [FolderResource::getUrl('index') => FolderResource::getNavigationLabel()];
    }
}
