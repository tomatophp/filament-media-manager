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
        $mediaIds = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->pluck('media_id')
            ->toArray();

        if (empty($mediaIds)) {
            return new Collection;
        }

        $query = Media::withoutGlobalScope('folder')->whereIn('id', $mediaIds);

        // Optionally filter by field name if stored in custom properties
        if ($fieldName) {
            $query->whereJsonContains('custom_properties->field_name', $fieldName);
        }

        return $query->get();
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
            ->get();

        foreach ($media as $mediaItem) {
            DB::table('media_has_models')->updateOrInsert(
                [
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                    'media_id' => $mediaItem->id,
                ],
                [
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
     * @param  array  $mediaUuids  Array of media UUIDs
     */
    public function syncMediaManagerMedia(array $mediaUuids): void
    {
        $this->detachMediaManagerMedia();
        $this->attachMediaManagerMedia($mediaUuids);
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
     */
    public function getFirstMediaManagerMedia(): ?Media
    {
        return $this->getMediaManagerMedia()->first();
    }

    /**
     * Get media URL from MediaManagerPicker (first item)
     *
     * @param  string  $conversion  Optional conversion name
     */
    public function getMediaManagerUrl(?string $conversion = null): ?string
    {
        $media = $this->getFirstMediaManagerMedia();

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Get all media URLs from MediaManagerPicker
     *
     * @param  string|null  $conversion  Optional conversion name
     */
    public function getMediaManagerUrls(?string $conversion = null): array
    {
        return $this->getMediaManagerMedia()
            ->map(fn ($media) => $conversion ? $media->getUrl($conversion) : $media->getUrl())
            ->toArray();
    }
}
