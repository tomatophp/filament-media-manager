<?php

namespace TomatoPHP\FilamentMediaManager\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TomatoPHP\FilamentMediaManager\Models\Media;

trait InteractsWithMediaManager
{
    /**
     * Get media attached via MediaManagerPicker by collection name
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerMedia(?string $collectionName = null): Collection
    {
        $query = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id);

        // Filter by collection name if specified
        if ($collectionName !== null) {
            $query->where('collection_name', $collectionName);
        } else {
            $query->whereNull('collection_name');
        }

        $mediaData = $query->orderBy('order_column')
            ->get(['media_id', 'order_column']);

        if ($mediaData->isEmpty()) {
            return new Collection;
        }

        $mediaIds = $mediaData->pluck('media_id')->toArray();
        $media = Media::withoutGlobalScope('folder')->whereIn('id', $mediaIds)->get()->keyBy('id');

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
     * @param  string|null  $collectionName  Optional collection name
     */
    public function attachMediaManagerMedia(array $mediaUuids, ?string $collectionName = null): void
    {
        $media = Media::withoutGlobalScope('folder')
            ->whereIn('uuid', $mediaUuids)
            ->get()
            ->keyBy('uuid');

        // Get current max order for this collection
        $maxOrderQuery = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id);

        if ($collectionName !== null) {
            $maxOrderQuery->where('collection_name', $collectionName);
        } else {
            $maxOrderQuery->whereNull('collection_name');
        }

        $maxOrder = $maxOrderQuery->max('order_column') ?? -1;
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
                    'collection_name' => $collectionName,
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
     * @param  string|null  $collectionName  Optional collection name
     */
    public function detachMediaManagerMedia(?array $mediaUuids = null, ?string $collectionName = null): void
    {
        $query = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id);

        if ($collectionName !== null) {
            $query->where('collection_name', $collectionName);
        } elseif ($collectionName === null && $mediaUuids === null) {
            // Only apply whereNull when detaching all and no collection specified
            $query->whereNull('collection_name');
        }

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
     * @param  string|null  $collectionName  Optional collection name
     */
    public function syncMediaManagerMedia(array $mediaUuids, ?string $collectionName = null): void
    {
        // Clear existing attachments for this collection
        $deleteQuery = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id);

        if ($collectionName !== null) {
            $deleteQuery->where('collection_name', $collectionName);
        } else {
            $deleteQuery->whereNull('collection_name');
        }

        $deleteQuery->delete();

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
                'collection_name' => $collectionName,
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
     * @param  string|null  $collectionName  Optional collection name
     */
    public function hasMediaManagerMedia(string $mediaUuid, ?string $collectionName = null): bool
    {
        $media = Media::withoutGlobalScope('folder')
            ->where('uuid', $mediaUuid)
            ->first();

        if (! $media) {
            return false;
        }

        $query = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->where('media_id', $media->id);

        if ($collectionName !== null) {
            $query->where('collection_name', $collectionName);
        }

        return $query->exists();
    }

    /**
     * Get first media item from MediaManagerPicker
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getFirstMediaManagerMedia(?string $collectionName = null): ?Media
    {
        return $this->getMediaManagerMedia($collectionName)->first();
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
        return $this->getMediaManagerMedia($collectionName)
            ->map(fn ($mediaItem) => $mediaItem->getUrl())
            ->toArray();
    }

    /**
     * Get responsive images for media from MediaManagerPicker
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     * @return \Illuminate\Support\Collection Collection of media with responsive images data
     */
    public function getMediaManagerResponsiveImages(?string $collectionName = null): Collection
    {
        $query = DB::table('media_has_models')
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->where('responsive_images', true);

        if ($collectionName !== null) {
            $query->where('collection_name', $collectionName);
        } else {
            $query->whereNull('collection_name');
        }

        $mediaData = $query->orderBy('order_column')
            ->get(['media_id', 'order_column']);

        if ($mediaData->isEmpty()) {
            return new Collection;
        }

        $mediaIds = $mediaData->pluck('media_id')->toArray();
        $media = Media::withoutGlobalScope('folder')->whereIn('id', $mediaIds)->get()->keyBy('id');

        // Return media sorted by order_column with responsive images
        $sorted = $mediaData->map(function ($item) use ($media) {
            $mediaItem = $media->get($item->media_id);
            if (! $mediaItem) {
                return null;
            }

            return [
                'media' => $mediaItem,
                'url' => $mediaItem->getUrl(),
                'responsive_urls' => $mediaItem->getResponsiveImageUrls(),
                'srcset' => $mediaItem->getSrcset(),
            ];
        })->filter()->values()->all();

        return new Collection($sorted);
    }

    /**
     * Get responsive images srcset attribute for first media item
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerSrcset(?string $collectionName = null): ?string
    {
        $responsiveImages = $this->getMediaManagerResponsiveImages($collectionName);

        if ($responsiveImages->isEmpty()) {
            return null;
        }

        return $responsiveImages->first()['srcset'] ?? null;
    }

    /**
     * Get all responsive images srcset attributes
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerSrcsets(?string $collectionName = null): array
    {
        return $this->getMediaManagerResponsiveImages($collectionName)
            ->pluck('srcset')
            ->filter()
            ->toArray();
    }

    /**
     * Get responsive image URLs for first media item
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getMediaManagerResponsiveUrls(?string $collectionName = null): ?array
    {
        $responsiveImages = $this->getMediaManagerResponsiveImages($collectionName);

        if ($responsiveImages->isEmpty()) {
            return null;
        }

        return $responsiveImages->first()['responsive_urls'] ?? null;
    }

    /**
     * Get all responsive image URLs for all media
     *
     * @param  string|null  $collectionName  Optional collection name to filter by
     */
    public function getAllMediaManagerResponsiveUrls(?string $collectionName = null): array
    {
        return $this->getMediaManagerResponsiveImages($collectionName)
            ->pluck('responsive_urls')
            ->filter()
            ->toArray();
    }
}
