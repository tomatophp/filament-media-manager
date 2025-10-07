<div class="fi-modal-content space-y-4">
    {{-- Search Input --}}
    <x-filament::input.wrapper>
        <x-filament::input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ trans('filament-media-manager::messages.picker.search') }}"
            autocomplete="off"
            name="media-search-{{ uniqid() }}"
            id="media-search-{{ uniqid() }}"
        >
            <x-slot name="prefix">
                <x-filament::icon
                    icon="heroicon-m-magnifying-glass"
                    class="h-5 w-5 text-gray-400 dark:text-gray-500"
                />
            </x-slot>
        </x-filament::input>
    </x-filament::input.wrapper>

    {{-- Navigation Bar --}}
    <div class="fi-section-header">
        <div class="fi-section-header-wrapper">
            {{-- Left: Folder Name --}}
            <div class="fi-section-header-heading">
                @if($currentFolder)
                    <h3 class="fi-section-header-title">
                        {{ $currentFolder->name }}
                    </h3>
                @else
                    <h3 class="fi-section-header-title">
                        {{ trans('filament-media-manager::messages.picker.title') }}
                    </h3>
                @endif
            </div>

            {{-- Right: Action Buttons --}}
            <div class="fi-section-header-actions">
                @if($currentFolder)
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-o-arrow-left"
                        wire:click="goBack"
                    >
                        {{ trans('filament-media-manager::messages.picker.back') }}
                    </x-filament::button>

                    {{ ($this->uploadMedia) }}
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Section Header Styles */
        .fi-section-header {
            border-radius: 0.5rem;
            border: 1px solid rgb(0 0 0 / 0.1);
            background-color: white;
            padding: 0.75rem 1rem;
        }

        .dark .fi-section-header {
            border-color: rgb(255 255 255 / 0.1);
            background-color: rgb(255 255 255 / 0.05);
        }

        .fi-section-header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .fi-section-header-heading {
            min-width: 0;
            flex: 1;
        }

        .fi-section-header-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1rem;
            font-weight: 700;
            color: rgb(17 24 39);
        }

        .dark .fi-section-header-title {
            color: white;
        }

        .fi-section-header-actions {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            gap: 0.5rem;
        }

        /* Grid Layout */
        .media-picker-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .media-picker-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 768px) {
            .media-picker-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .media-picker-grid {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }
    </style>

    {{-- Content --}}
    <div class="space-y-6">
        {{-- Folders Grid --}}
        @if($folders->count() > 0)
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ trans('filament-media-manager::messages.picker.folders') }}
                </h3>
                <div class="media-picker-grid">
                    @foreach($folders as $folder)
                        <button
                            type="button"
                            wire:click="openFolder({{ $folder->id }})"
                            style="background: transparent; border: none; cursor: pointer; padding: 0; width: 100%;"
                        >
                            <style>
                                .folder-container-picker-{{$folder->id}} {
                                    display: flex;
                                    flex-direction: column;
                                    justify-content: center;
                                    align-items: center;
                                    gap: 0.5rem;
                                    padding: 0.5rem;
                                    border-radius: 0.5rem;
                                    transition: background-color 0.15s ease;
                                }

                                .folder-container-picker-{{$folder->id}}:hover {
                                    background-color: rgb(249 250 251);
                                }

                                .dark .folder-container-picker-{{$folder->id}}:hover {
                                    background-color: rgb(55 65 81 / 0.5);
                                }

                                .folder-icon-picker-{{$folder->id}} {
                                    width: 100px;
                                    height: 70px;
                                    background-color: {{$folder->color ?? '#f3c623'}};
                                    border-radius: 5px;
                                    position: relative;
                                    margin: 10px;
                                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                }

                                .folder-icon-picker-{{$folder->id}}::before {
                                    content: "";
                                    width: 40px;
                                    height: 10px;
                                    background-color: {{$folder->color ?? '#f3c623'}};
                                    border-radius: 5px 5px 0 0;
                                    position: absolute;
                                    top: -10px;
                                    left: 10px;
                                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                                }

                                .folder-icon-content-picker-{{$folder->id}} {
                                    color: white;
                                    width: 2rem;
                                    height: 2rem;
                                }

                                .folder-lock-badge-picker-{{$folder->id}} {
                                    position: absolute;
                                    top: -8px;
                                    right: -8px;
                                    background-color: rgb(239 68 68);
                                    border-radius: 9999px;
                                    padding: 0.375rem;
                                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    z-index: 10;
                                }

                                .dark .folder-lock-badge-picker-{{$folder->id}} {
                                    background-color: rgb(220 38 38);
                                }

                                .folder-lock-icon-picker-{{$folder->id}} {
                                    width: 1rem;
                                    height: 1rem;
                                    color: white;
                                }

                                .folder-info-picker-{{$folder->id}} {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                }

                                .folder-name-picker-{{$folder->id}} {
                                    font-weight: 600;
                                    font-size: 0.875rem;
                                    line-height: 1.25rem;
                                    text-align: center;
                                    max-width: 120px;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                    color: rgb(17 24 39);
                                }

                                .dark .folder-name-picker-{{$folder->id}} {
                                    color: rgb(249 250 251);
                                }

                                .folder-date-picker-{{$folder->id}} {
                                    color: rgb(107 114 128);
                                    font-size: 0.75rem;
                                    line-height: 1rem;
                                    text-align: center;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                }

                                .dark .folder-date-picker-{{$folder->id}} {
                                    color: rgb(156 163 175);
                                }
                            </style>
                            <div class="folder-container-picker-{{$folder->id}}">
                                <div class="folder-icon-picker-{{$folder->id}}">
                                    @if($folder->icon)
                                        <x-icon name="{{$folder->icon}}" class="folder-icon-content-picker-{{$folder->id}}"/>
                                    @endif
                                    @if($folder->is_protected)
                                        <div class="folder-lock-badge-picker-{{$folder->id}}">
                                            <x-icon name="heroicon-o-lock-closed" class="folder-lock-icon-picker-{{$folder->id}}" />
                                        </div>
                                    @endif
                                </div>
                                <div class="folder-info-picker-{{$folder->id}}">
                                    <h1 class="folder-name-picker-{{$folder->id}}"
                                        x-data
                                        x-tooltip="{
                                            content: '{{ addslashes($folder->name) }}',
                                            theme: $root.closest('.dark') ? 'dark' : 'light',
                                        }">
                                        {{ $folder->name }}
                                    </h1>
                                    <p class="folder-date-picker-{{$folder->id}}">
                                        {{ $folder->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Media Grid --}}
        @if($media->count() > 0)
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ trans('filament-media-manager::messages.picker.media_files') }}
                </h3>
                <div class="media-picker-grid">
                    @foreach($media as $mediaItem)
                        <div
                            wire:key="media-{{ $mediaItem->uuid }}"
                            class="relative"
                        >
                            <button
                                type="button"
                                wire:click="toggleMediaSelection('{{ $mediaItem->uuid }}')"
                                style="background: transparent; border: none; cursor: pointer; padding: 0; width: 100%;"
                            >
                                @php
                                    $extension = strtolower(pathinfo($mediaItem->file_name, PATHINFO_EXTENSION));
                                    $title = $mediaItem->hasCustomProperty('title') ? (!empty($mediaItem->getCustomProperty('title')) ? $mediaItem->getCustomProperty('title') : $mediaItem->name) : $mediaItem->name;

                                    // File type icon mapping
                                    $fileIcons = [
                                        'pdf' => ['icon' => 'heroicon-o-document-text', 'color' => '#ef4444'],
                                        'doc' => ['icon' => 'heroicon-o-document-text', 'color' => '#3b82f6'],
                                        'docx' => ['icon' => 'heroicon-o-document-text', 'color' => '#3b82f6'],
                                        'xls' => ['icon' => 'heroicon-o-table-cells', 'color' => '#22c55e'],
                                        'xlsx' => ['icon' => 'heroicon-o-table-cells', 'color' => '#22c55e'],
                                        'ppt' => ['icon' => 'heroicon-o-presentation-chart-bar', 'color' => '#f97316'],
                                        'pptx' => ['icon' => 'heroicon-o-presentation-chart-bar', 'color' => '#f97316'],
                                        'zip' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                        'rar' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                        'json' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#eab308'],
                                        'xml' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#8b5cf6'],
                                        'html' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#f97316'],
                                        'css' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                        'js' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#eab308'],
                                        'php' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#8b5cf6'],
                                        'svg' => ['icon' => 'heroicon-o-photo', 'color' => '#f59e0b'],
                                    ];

                                    $fileIcon = $fileIcons[$extension] ?? ['icon' => 'heroicon-o-document', 'color' => '#9ca3af'];
                                @endphp

                                <style>
                                    .media-card-picker-{{$mediaItem->id}} {
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        gap: 0.5rem;
                                        padding: 0.5rem;
                                        border-radius: 0.5rem;
                                        transition: background-color 0.15s ease;
                                        position: relative;
                                    }

                                    .media-card-picker-{{$mediaItem->id}}:hover {
                                        background-color: rgb(249 250 251);
                                    }

                                    .dark .media-card-picker-{{$mediaItem->id}}:hover {
                                        background-color: rgb(55 65 81 / 0.5);
                                    }

                                    .media-card-picker-{{$mediaItem->id}}.selected {
                                        background-color: rgb(239 246 255);
                                        border: 2px solid rgb(59 130 246);
                                    }

                                    .dark .media-card-picker-{{$mediaItem->id}}.selected {
                                        background-color: rgb(37 99 235 / 0.2);
                                        border-color: rgb(96 165 250);
                                    }

                                    .media-preview-picker-{{$mediaItem->id}} {
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        width: 100px;
                                        height: 70px;
                                        position: relative;
                                        overflow: hidden;
                                        margin: 10px;
                                    }

                                    .media-preview-with-bg-picker-{{$mediaItem->id}} {
                                        border-radius: 5px;
                                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                                        background-color: rgb(243 244 246);
                                    }

                                    .dark .media-preview-with-bg-picker-{{$mediaItem->id}} {
                                        background-color: rgb(31 41 55);
                                    }

                                    .media-preview-picker-{{$mediaItem->id}} img {
                                        width: 100%;
                                        height: 100%;
                                        object-fit: cover;
                                        border-radius: 5px;
                                    }

                                    .media-preview-picker-{{$mediaItem->id}} video {
                                        width: 100%;
                                        height: 100%;
                                        object-fit: cover;
                                        border-radius: 5px;
                                    }

                                    .media-icon-large-picker-{{$mediaItem->id}} {
                                        width: 4rem;
                                        height: 4rem;
                                    }

                                    .media-file-icon-picker-{{$mediaItem->id}} {
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        gap: 0.25rem;
                                        width: 100%;
                                        height: 100%;
                                    }

                                    .media-file-icon-image-picker-{{$mediaItem->id}} {
                                        width: 3.5rem;
                                        height: 3.5rem;
                                    }

                                    .media-file-extension-picker-{{$mediaItem->id}} {
                                        font-size: 0.625rem;
                                        font-weight: 600;
                                        text-transform: uppercase;
                                        color: rgb(107 114 128);
                                    }

                                    .dark .media-file-extension-picker-{{$mediaItem->id}} {
                                        color: rgb(156 163 175);
                                    }

                                    .media-info-picker-{{$mediaItem->id}} {
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                    }

                                    .media-title-picker-{{$mediaItem->id}} {
                                        font-weight: 600;
                                        font-size: 0.875rem;
                                        line-height: 1.25rem;
                                        text-align: center;
                                        max-width: 120px;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                        color: rgb(17 24 39);
                                    }

                                    .dark .media-title-picker-{{$mediaItem->id}} {
                                        color: rgb(249 250 251);
                                    }

                                    .media-timestamp-picker-{{$mediaItem->id}} {
                                        color: rgb(107 114 128);
                                        font-size: 0.75rem;
                                        line-height: 1rem;
                                        text-align: center;
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                    }

                                    .dark .media-timestamp-picker-{{$mediaItem->id}} {
                                        color: rgb(156 163 175);
                                    }

                                    .media-check-badge-picker-{{$mediaItem->id}} {
                                        position: absolute;
                                        top: 2px;
                                        right: 2px;
                                        background-color: rgb(59 130 246);
                                        border-radius: 9999px;
                                        padding: 0.25rem;
                                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        z-index: 10;
                                    }

                                    .media-check-icon-picker-{{$mediaItem->id}} {
                                        width: 0.875rem;
                                        height: 0.875rem;
                                        color: white;
                                    }
                                </style>

                                <div class="media-card-picker-{{$mediaItem->id}} {{ in_array($mediaItem->uuid, $selectedMedia) ? 'selected' : '' }}">
                                    <div class="media-preview-picker-{{$mediaItem->id}} {{ str($mediaItem->mime_type)->contains(['image', 'video']) ? 'media-preview-with-bg-picker-' . $mediaItem->id : '' }}"
                                         x-data>
                                        @if(str($mediaItem->mime_type)->contains('image'))
                                            <img src="{{ $mediaItem->getUrl() }}" alt="{{ $title }}" />
                                        @elseif(str($mediaItem->mime_type)->contains('video'))
                                            <video src="{{ $mediaItem->getUrl() }}"></video>
                                        @elseif(str($mediaItem->mime_type)->contains('audio'))
                                            <x-icon name="heroicon-o-musical-note" class="media-icon-large-picker-{{$mediaItem->id}}" style="color: #ec4899;" />
                                        @else
                                            <div class="media-file-icon-picker-{{$mediaItem->id}}">
                                                <x-icon :name="$fileIcon['icon']" class="media-file-icon-image-picker-{{$mediaItem->id}}" style="color: {{ $fileIcon['color'] }};" />
                                                <span class="media-file-extension-picker-{{$mediaItem->id}}">{{ $extension }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="media-info-picker-{{$mediaItem->id}}">
                                        <h1 class="media-title-picker-{{$mediaItem->id}}"
                                            x-data
                                            x-tooltip="{
                                                content: '{{ addslashes($title) }}',
                                                theme: $root.closest('.dark') ? 'dark' : 'light',
                                            }">
                                            {{ $title }}
                                        </h1>
                                        <p class="media-timestamp-picker-{{$mediaItem->id}}">
                                            {{ $mediaItem->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    @if(in_array($mediaItem->uuid, $selectedMedia))
                                        <div class="media-check-badge-picker-{{$mediaItem->id}}">
                                            <x-icon name="heroicon-m-check" class="media-check-icon-picker-{{$mediaItem->id}}" />
                                        </div>
                                    @endif
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Empty State --}}
        @if($folders->count() === 0 && $media->count() === 0)
            <div class="fi-ta-empty-state px-6 py-12">
                <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                    <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                        <x-filament::icon
                            icon="heroicon-o-folder-open"
                            class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                        />
                    </div>
                    <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        {{ trans('filament-media-manager::messages.picker.empty') }}
                    </h4>
                </div>
            </div>
        @endif
    </div>

    {{-- Selected Items Preview (Multiple Mode Only) --}}
    @if($isMultiple && count($selectedMedia) > 0)
        @php
            // Ensure $selectedMedia is a flat array of UUIDs
            $uuids = collect($selectedMedia)->flatten()->filter()->unique()->toArray();
            $selectedMediaItems = \TomatoPHP\FilamentMediaManager\Models\Media::withoutGlobalScope('folder')->whereIn('uuid', $uuids)->get();
        @endphp
        <style>
            .fi-selected-preview-section {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgb(0 0 0 / 0.1);
            }

            .fi-selected-preview-section:where(.dark,.dark *) {
                border-top-color: rgb(255 255 255 / 0.1);
            }

            .fi-selected-preview-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 0.75rem;
            }

            .fi-selected-preview-title {
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(17 24 39);
            }

            .fi-selected-preview-title:where(.dark,.dark *) {
                color: white;
            }

            .fi-selected-list-container {
                overflow: hidden;
                border-radius: 0.5rem;
                border: 1px solid rgb(0 0 0 / 0.1);
                background-color: white;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                padding: 0.5rem;
            }

            .fi-selected-list-container:where(.dark,.dark *) {
                border-color: rgb(255 255 255 / 0.1);
                background-color: var(--gray-950);
            }

            .fi-selected-list-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.75rem;
                transition: background-color 0.15s;
                border-radius: 0.375rem;
            }

            .fi-selected-list-item:not(:first-child) {
                margin-top: 0.5rem;
            }

            .fi-selected-list-item:hover {
                background-color: rgb(249 250 251);
            }

            .fi-selected-list-item:where(.dark,.dark *):hover {
                background-color: rgb(255 255 255 / 0.05);
            }

            .fi-selected-preview-thumb {
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

            .fi-selected-preview-thumb:where(.dark,.dark *) {
                border-color: rgb(255 255 255 / 0.1);
                background-color: var(--gray-800);
            }

            .fi-selected-preview-thumb img {
                height: 100%;
                width: 100%;
                object-fit: cover;
                border-radius: 0.25rem;
            }

            .fi-selected-icon {
                height: 1.75rem;
                width: 1.75rem;
                color: rgb(107 114 128);
            }

            .fi-selected-icon:where(.dark,.dark *) {
                color: rgb(156 163 175);
            }

            .fi-selected-icon-danger {
                height: 1.75rem;
                width: 1.75rem;
                color: rgb(239 68 68);
            }

            .fi-selected-file-info {
                min-width: 0;
                flex: 1;
            }

            .fi-selected-file-name {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 0.875rem;
                font-weight: 600;
                color: rgb(17 24 39);
            }

            .fi-selected-file-name:where(.dark,.dark *) {
                color: white;
            }

            .fi-selected-file-filename {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 0.75rem;
                color: rgb(107 114 128);
            }

            .fi-selected-file-filename:where(.dark,.dark *) {
                color: rgb(156 163 175);
            }

            .fi-selected-file-meta {
                margin-top: 0.125rem;
                font-size: 0.75rem;
                color: rgb(107 114 128);
            }

            .fi-selected-file-meta:where(.dark,.dark *) {
                color: rgb(156 163 175);
            }

            .fi-action-buttons {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                border-top: 1px solid rgb(0 0 0 / 0.1);
                padding-top: 1rem;
            }

            .fi-action-buttons:where(.dark,.dark *) {
                border-top-color: rgb(255 255 255 / 0.1);
            }

            .fi-action-buttons-right {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
        </style>

        <div class="fi-selected-preview-section">
            <div class="fi-selected-preview-header">
                <h4 class="fi-selected-preview-title">
                    {{ trans('filament-media-manager::messages.picker.selected') }} ({{ count($selectedMedia) }})
                </h4>
            </div>

            {{-- List Container --}}
            <div class="fi-selected-list-container">
                @foreach($selectedMediaItems as $mediaItem)
                    <div class="fi-selected-list-item">
                        {{-- Preview --}}
                        <div class="fi-selected-preview-thumb">
                            @if(str($mediaItem->mime_type)->contains('image'))
                                <img
                                    src="{{ $mediaItem->getUrl() }}"
                                    alt="{{ $mediaItem->file_name }}"
                                />
                            @elseif(str($mediaItem->mime_type)->contains('video'))
                                <x-filament::icon
                                    icon="heroicon-o-video-camera"
                                    class="fi-selected-icon"
                                />
                            @elseif(str($mediaItem->mime_type)->contains('audio'))
                                <x-filament::icon
                                    icon="heroicon-o-musical-note"
                                    class="fi-selected-icon"
                                />
                            @elseif(str($mediaItem->mime_type)->contains('pdf'))
                                <x-filament::icon
                                    icon="heroicon-o-document-text"
                                    class="fi-selected-icon-danger"
                                />
                            @else
                                <x-filament::icon
                                    icon="heroicon-o-document"
                                    class="fi-selected-icon"
                                />
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="fi-selected-file-info">
                            <p class="fi-selected-file-name">
                                {{ $mediaItem->name }}
                            </p>
                            <p class="fi-selected-file-filename">
                                {{ $mediaItem->file_name }}
                            </p>
                            <p class="fi-selected-file-meta">
                                {{ number_format($mediaItem->size / 1024, 2) }} KB
                                @if($mediaItem->hasCustomProperty('description') && $mediaItem->getCustomProperty('description'))
                                    <span style="margin: 0 0.25rem;">•</span>
                                    {{ str($mediaItem->getCustomProperty('description'))->limit(50) }}
                                @endif
                            </p>
                        </div>

                        {{-- Remove Button --}}
                        <x-filament::icon-button
                            icon="heroicon-m-x-mark"
                            color="danger"
                            size="sm"
                            wire:click="removeSelection('{{ $mediaItem->uuid }}')"
                            :label="trans('filament-media-manager::messages.picker.remove')"
                        />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    @if($isMultiple)
        <div class="fi-action-buttons">
            @if(count($selectedMedia) > 0)
                {{-- Left: Selected Count Badge --}}
                <div>
                    <x-filament::badge color="primary" size="lg">
                        {{ count($selectedMedia) }} {{ trans('filament-media-manager::messages.picker.selected') }}
                    </x-filament::badge>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="fi-action-buttons-right">
                    <x-filament::button
                        type="button"
                        color="danger"
                        outlined
                        wire:click="$set('selectedMedia', [])"
                    >
                        {{ trans('filament-media-manager::messages.picker.clear_all') }}
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        wire:click="selectMedia"
                    >
                        {{ trans('filament-media-manager::messages.picker.select') }}
                    </x-filament::button>
                </div>
            @endif
        </div>
    @endif

    <x-filament-actions::modals />
</div>
