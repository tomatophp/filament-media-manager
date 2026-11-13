<?php

namespace TomatoPHP\FilamentMediaManager\Services;

use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;

/**
 * The trail from the folders list down to a folder, for the page breadcrumbs and the media picker.
 *
 * Folders nest in two ways: a sub folder points to its parent through model_type = Folder and model_id, and a
 * folder of a model record (model_type and model_id set) sits in a collection folder (model_type and collection
 * set), which sits in the folder of its model type (only model_type set). On the pages, those two levels are
 * the folders list filtered by model_type and collection, like a click on them does.
 */
class FolderBreadcrumbs
{
    /**
     * @return array<string, string> label by url, the folder itself last
     */
    public static function forFolder(Folder $folder): array
    {
        $breadcrumbs = static::root();

        foreach (static::levels($folder) as $level) {
            $url = isset($level['filters'])
                ? FolderResource::getUrl('index', $level['filters'])
                : MediaResource::getUrl('index', ['folder_id' => $level['folder']?->getKey()]);

            $breadcrumbs[$url] = $level['label'];
        }

        return $breadcrumbs;
    }

    /**
     * The folders page, filtered to a model type and a collection
     *
     * @return array<string, string>
     */
    public static function forList(?string $modelType, ?string $collection): array
    {
        $breadcrumbs = static::root();

        foreach ($modelType ? static::modelTypeLevels($modelType, $collection) : [] as $level) {
            $breadcrumbs[FolderResource::getUrl('index', $level['filters'])] = $level['label'];
        }

        return $breadcrumbs;
    }

    /**
     * The folders above a folder in the media picker, and the folder itself last. A level without a folder
     * (a model type or collection without its own folder) has no id, the picker can't open it.
     *
     * @return list<array{id: ?int, label: string}>
     */
    public static function forPicker(Folder $folder): array
    {
        return array_map(
            fn (array $level): array => ['id' => $level['folder']?->getKey(), 'label' => $level['label']],
            static::levels($folder),
        );
    }

    /**
     * @return list<array{label: string, folder: ?Folder, filters?: array<string, string>}>
     */
    protected static function levels(Folder $folder): array
    {
        $levels = [];
        $visited = [];
        $current = $folder;

        while ($current && ! in_array($current->getKey(), $visited, true)) {
            $visited[] = $current->getKey();

            array_unshift($levels, ['label' => $current->name, 'folder' => $current]);

            if ($current->model_type === Folder::class && $current->model_id) {
                // A folder that is not accessible (FolderAccess scope) ends the trail
                $current = Folder::query()->find($current->model_id);

                continue;
            }

            if ($current->model_type && $current->model_id) {
                $levels = [...static::modelTypeLevels($current->model_type, $current->collection), ...$levels];
            }

            break;
        }

        return $levels;
    }

    /**
     * @return list<array{label: string, folder: ?Folder, filters: array<string, string>}>
     */
    protected static function modelTypeLevels(string $modelType, ?string $collection): array
    {
        $typeFolder = Folder::query()
            ->where('model_type', $modelType)
            ->whereNull('model_id')
            ->whereNull('collection')
            ->first();

        $levels = [[
            'label' => $typeFolder->name ?? (string) str($modelType)->afterLast('\\')->headline(),
            'folder' => $typeFolder,
            'filters' => ['model_type' => $modelType],
        ]];

        if (filled($collection)) {
            $collectionFolder = Folder::query()
                ->where('model_type', $modelType)
                ->whereNull('model_id')
                ->where('collection', $collection)
                ->first();

            $levels[] = [
                'label' => $collectionFolder->name ?? (string) str($collection)->headline(),
                'folder' => $collectionFolder,
                'filters' => ['model_type' => $modelType, 'collection' => $collection],
            ];
        }

        return $levels;
    }

    /**
     * @return array<string, string>
     */
    protected static function root(): array
    {
        return [FolderResource::getUrl('index') => FolderResource::getNavigationLabel()];
    }
}
