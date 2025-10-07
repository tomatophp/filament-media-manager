<?php

namespace TomatoPHP\FilamentMediaManager\Models;

use Illuminate\Database\Eloquent\Builder;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    protected static function booted(): void
    {
        static::addGlobalScope('folder', function (Builder $query) {
            $folder = Folder::find(session()->get('folder_id'));
            if ($folder) {
                if (! $folder->model_type) {
                    $query->where('collection_name', $folder->collection);
                } else {
                    $query
                        ->where('model_type', $folder->model_type)
                        ->where('model_id', $folder->model_id)
                        ->where('collection_name', $folder->collection);
                }
            }
        });
    }
}
