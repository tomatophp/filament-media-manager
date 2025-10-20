<?php

namespace TomatoPHP\FilamentMediaManager\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentMediaManager\Models\Media;

trait InteractsWithMediaManager
{
    /**
     * Get media attached via MediaManagerPicker by field name
     *
     * @param  string|null  $fieldName  Optional field name to filter by custom property
     */
    public function getMediaManagerMedia(?string $fieldName = null): Collection
    {
        $mediaData = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->orderBy('order_column')
            ->get(['media_id', 'order_column']);

        if ($mediaData->isEmpty()) {
            return new Collection;
        }

        $mediaIds = $mediaData->pluck('media_id')->toArray();
        $query = Media::withoutGlobalScope('folder')->whereIn('id', $mediaIds);

        // Optionally filter by field name if stored in custom properties
        if ($fieldName) {
            $query->whereJsonContains('custom_properties->field_name', $fieldName);
        }

        $media = $query->get()->keyBy('id');

        // Return media sorted by order_column as Eloquent Collection
        $sorted = $mediaData->map(function ($item) use ($media) {
            return $media->get($item->media_id);
        })->filter()->values()->all();

        return new Collection($sorted);
    }

    /**
     * Get media attached via MediaManagerPicker by UUIDs
     *
     * @param  array  $uuids  Array of media UUIDs
     */
    public function getMediaManagerMediaByUuids(array $uuids): Collection
    {
        if (empty($uuids)) {
            return new Collection;
        }

        return Media::withoutGlobalScope('folder')
            ->whereIn('uuid', $uuids)
            ->get();
    }

    /**
     * Get media attached via MediaManagerInput (using Spatie's media library)
     *
     * @param  string  $collectionName  The media collection name
     */
    public function getMediaManagerInputMedia(string $collectionName = 'default'): Collection
    {
        if (! method_exists($this, 'getMedia')) {
            return new Collection;
        }

        return $this->getMedia($collectionName);
    }

    /**
     * Attach media to model via MediaManagerPicker
     *
     * @param  array  $mediaUuids  Array of media UUIDs
     */
    public function attachMediaManagerMedia(array $mediaUuids): void
    {
        $media = Media::withoutGlobalScope('folder')
            ->whereIn('uuid', $mediaUuids)
            ->get()
            ->keyBy('uuid');

        // Get current max order
        $maxOrder = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->max('order_column') ?? -1;

        $order = $maxOrder + 1;

        foreach ($mediaUuids as $uuid) {
            $mediaItem = $media->get($uuid);
            if (! $mediaItem) {
                continue;
            }

            DB::table('media_has_models')->updateOrInsert(
                [
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                    'media_id' => $mediaItem->id,
                ],
                [
                    'order_column' => $order++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Detach media from model via MediaManagerPicker
     *
     * @param  array|null  $mediaUuids  Array of media UUIDs to detach, or null to detach all
     */
    public function detachMediaManagerMedia(?array $mediaUuids = null): void
    {
        $query = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id);

        if ($mediaUuids !== null) {
            $mediaIds = Media::withoutGlobalScope('folder')
                ->whereIn('uuid', $mediaUuids)
                ->pluck('id')
                ->toArray();

            if (! empty($mediaIds)) {
                $query->whereIn('media_id', $mediaIds);
            }
        }

        $query->delete();
    }

    /**
     * Sync media with model (detach all and attach new)
     *
     * @param  array  $mediaUuids  Array of media UUIDs (order preserved)
     */
    public function syncMediaManagerMedia(array $mediaUuids): void
    {
        // Clear all existing attachments
        DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->delete();

        if (empty($mediaUuids)) {
            return;
        }

        // Attach new media with order
        $media = Media::withoutGlobalScope('folder')
            ->whereIn('uuid', $mediaUuids)
            ->get()
            ->keyBy('uuid');

        $order = 0;
        foreach ($mediaUuids as $uuid) {
            $mediaItem = $media->get($uuid);
            if (! $mediaItem) {
                continue;
            }

            DB::table('media_has_models')->insert([
                'model_type' => get_class($this),
                'model_id' => $this->id,
                'media_id' => $mediaItem->id,
                'order_column' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Check if model has specific media attached
     *
     * @param  string  $mediaUuid  Media UUID to check
     */
    public function hasMediaManagerMedia(string $mediaUuid): bool
    {
        $media = Media::withoutGlobalScope('folder')
            ->where('uuid', $mediaUuid)
            ->first();

        if (! $media) {
            return false;
        }

        return DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->where('media_id', $media->id)
            ->exists();
    }

    /**
     * Get first media item from MediaManagerPicker
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getFirstMediaManagerMedia(?string $collectionName = null): ?Media
    {
        $media = $this->getMediaManagerMedia();

        if ($collectionName) {
            $media = $media->where('collection_name', $collectionName);
        }

        return $media->first();
    }

    /**
     * Get media URL from MediaManagerPicker (first item)
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerUrl(?string $collectionName = null): ?string
    {
        $media = $this->getFirstMediaManagerMedia($collectionName);

        if (! $media) {
            return null;
        }

        return $media->getUrl();
    }

    /**
     * Get all media URLs from MediaManagerPicker
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerUrls(?string $collectionName = null): array
    {
        $media = $this->getMediaManagerMedia();

        if ($collectionName) {
            $media = $media->where('collection_name', $collectionName);
        }

        return $media->map(fn ($mediaItem) => $mediaItem->getUrl())
            ->toArray();
    }
}
