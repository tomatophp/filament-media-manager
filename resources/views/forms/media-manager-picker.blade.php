@php
    $statePath = $getStatePath();
    $isMultiple = $isMultiple();
    $state = $getState();
    $mediaItems = [];

    if ($state) {
        $uuids = is_array($state) ? $state : [$state];
        $mediaItems = \TomatoPHP\FilamentMediaManager\Models\Media::whereIn('uuid', $uuids)->get();
    }
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        .fi-picker-list-container {
            overflow: hidden;
            border-radius: 0.5rem;
            border: 1px solid rgb(0 0 0 / 0.1);
            background-color: white;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            margin-top: 1rem;
            padding: 0.5rem;
        }

        .fi-picker-list-container:where(.dark,.dark *) {
            border-color: rgb(255 255 255 / 0.1);
            background-color: var(--gray-950);
        }

        .fi-picker-list-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            transition: background-color 0.15s;
            border-radius: 0.375rem;
        }

        .fi-picker-list-item:not(:first-child) {
            margin-top: 0.5rem;
        }

        .fi-picker-list-item:hover {
            background-color: rgb(249 250 251);
        }

        .fi-picker-list-item:where(.dark,.dark *):hover {
            background-color: rgb(255 255 255 / 0.05);
        }

        .fi-picker-preview-thumb {
            display: flex;
            width: 3.5rem;
            height: 3.5rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 0.5rem;
            border: 1px solid rgb(0 0 0 / 0.1);
            background-color: rgb(249 250 251);
            padding: 0.25rem;
        }

        .fi-picker-preview-thumb:where(.dark,.dark *) {
            border-color: rgb(255 255 255 / 0.1);
            background-color: var(--gray-800);
        }

        .fi-picker-preview-thumb img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            border-radius: 0.25rem;
        }

        .fi-picker-icon {
            height: 1.75rem;
            width: 1.75rem;
            color: rgb(107 114 128);
        }

        .fi-picker-icon:where(.dark,.dark *) {
            color: rgb(156 163 175);
        }

        .fi-picker-icon-danger {
            height: 1.75rem;
            width: 1.75rem;
            color: rgb(239 68 68);
        }

        .fi-picker-file-info {
            min-width: 0;
            flex: 1;
        }

        .fi-picker-file-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(17 24 39);
        }

        .fi-picker-file-name:where(.dark,.dark *) {
            color: white;
        }

        .fi-picker-file-filename {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.75rem;
            color: rgb(107 114 128);
        }

        .fi-picker-file-filename:where(.dark,.dark *) {
            color: rgb(156 163 175);
        }

        .fi-picker-file-meta {
            margin-top: 0.125rem;
            font-size: 0.75rem;
            color: rgb(107 114 128);
        }

        .fi-picker-file-meta:where(.dark,.dark *) {
            color: rgb(156 163 175);
        }

        .fi-picker-empty-state-container {
            overflow: hidden;
            border-radius: 0.5rem;
            border: 1px solid rgb(0 0 0 / 0.1);
            background-color: white;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            margin-top: 1rem;
        }

        .fi-picker-empty-state-container:where(.dark,.dark *) {
            border-color: rgb(255 255 255 / 0.1);
            background-color: var(--gray-950);
        }
    </style>

    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            pickerKey: '{{ $getId() }}'
        }"
        x-init="
            let isProcessing = false;

            window.addEventListener('media-selected-' + pickerKey, (event) => {
                if (isProcessing) return;
                isProcessing = true;

                const newState = event.detail?.media || event.detail;
                state = newState;

                $wire.set('{{ $statePath }}', newState).then(() => {
                    if ($wire.mountedActions && $wire.mountedActions.length > 0) {
                        $wire.mountedActions = [];
                        $wire.mountedActionsData = [];
                    }

                    setTimeout(() => { isProcessing = false; }, 300);
                });
            });
        "
        class="space-y-3"
    >
        {{-- Preview Section --}}
        @if(!empty($mediaItems) && $mediaItems->count() > 0)
            {{-- List Container --}}
            <div class="fi-picker-list-container">
                @foreach($mediaItems as $media)
                    <div class="fi-picker-list-item">
                        {{-- Preview --}}
                        <div class="fi-picker-preview-thumb">
                            @if(str_starts_with($media->mime_type, 'image/'))
                                <img
                                    src="{{ $media->getUrl() }}"
                                    alt="{{ $media->name }}"
                                />
                            @elseif(str($media->mime_type)->contains('video'))
                                <x-filament::icon
                                    icon="heroicon-o-video-camera"
                                    class="fi-picker-icon"
                                />
                            @elseif(str($media->mime_type)->contains('audio'))
                                <x-filament::icon
                                    icon="heroicon-o-musical-note"
                                    class="fi-picker-icon"
                                />
                            @elseif(str($media->mime_type)->contains('pdf'))
                                <x-filament::icon
                                    icon="heroicon-o-document-text"
                                    class="fi-picker-icon-danger"
                                />
                            @else
                                <x-filament::icon
                                    icon="heroicon-o-document"
                                    class="fi-picker-icon"
                                />
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="fi-picker-file-info">
                            <p class="fi-picker-file-name">
                                {{ $media->name }}
                            </p>
                            <p class="fi-picker-file-filename">
                                {{ $media->file_name }}
                            </p>
                            <p class="fi-picker-file-meta">
                                {{ number_format($media->size / 1024, 2) }} KB
                                @if($media->hasCustomProperty('description') && $media->getCustomProperty('description'))
                                    <span style="margin: 0 0.25rem;">•</span>
                                    {{ str($media->getCustomProperty('description'))->limit(50) }}
                                @endif
                            </p>
                        </div>

                        {{-- Remove Button --}}
                        {{ $getAction('removeMediaItem')->arguments(['uuid' => $media->uuid]) }}
                    </div>
                @endforeach
            </div>
        @else
            <div class="fi-picker-empty-state-container">
                <div class="fi-ta-empty-state px-6 py-12">
                    <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                        <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                            <x-filament::icon
                                icon="heroicon-o-photo"
                                class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                            />
                        </div>
                        <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            {{ trans('filament-media-manager::messages.picker.no_media_selected') }}
                        </h4>
                        <p class="fi-ta-empty-state-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Click "Browse Media" to select files') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-wrap items-center gap-2" style="margin-top: 1rem;">
            {{ $getAction('getBrowseAction') }}

            @if(!empty($mediaItems) && $mediaItems->count() > 0)
                {{ $getAction('getRemoveAction') }}
            @endif
        </div>
    </div>
</x-dynamic-component>
