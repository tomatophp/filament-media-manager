@php
    $currentFolder = \TomatoPHP\FilamentMediaManager\Models\Folder::find($this->folder_id);
    if(filament('filament-media-manager')->allowSubFolders){
        $folders = \TomatoPHP\FilamentMediaManager\Models\Folder::query()
            ->where('model_type', \TomatoPHP\FilamentMediaManager\Models\Folder::class)
            ->where('model_id', $this->folder_id)
            ->get();
    }
    else {
        $folders = [];
    }

@endphp

<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
        padding: 1rem;
        background-color: rgb(249 250 251);
        border-radius: 0.5rem;
    }

    .media-grid:where(.dark, .dark *) {
        background-color: transparent;
    }

    @media (min-width: 640px) {
        .media-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 768px) {
        .media-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .media-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .media-grid {
            grid-template-columns: repeat(8, minmax(0, 1fr));
        }
    }

    .media-trigger-slot {
        width: 100%;
        height: 100%;
    }

    .media-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: background-color 0.15s ease;
    }

    .media-card:hover {
        background-color: rgb(249 250 251);
    }

    .media-card:hover:where(.dark, .dark *) {
        background-color: rgb(55 65 81 / 0.5);
    }

    .media-preview {
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

    .media-preview-with-bg {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        background-color: rgb(243 244 246);
    }

    .media-preview-with-bg:where(.dark, .dark *) {
        background-color: rgb(31 41 55);
    }

    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .media-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .media-icon-large {
        width: 4rem;
        height: 4rem;
    }

    .media-file-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        width: 100%;
        height: 100%;
    }

    .media-file-icon-image {
        width: 3.5rem;
        height: 3.5rem;
    }

    .media-file-extension {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        color: rgb(107 114 128);
    }

    .media-file-extension:where(.dark, .dark *) {
        color: rgb(156 163 175);
    }

    .media-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .media-title {
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

    .media-title:where(.dark, .dark *) {
        color: rgb(249 250 251);
    }

    .media-subtitle {
        font-weight: 700;
        color: rgb(17 24 39);
    }

    .media-subtitle:where(.dark, .dark *) {
        color: rgb(249 250 251);
    }

    .flex-start {
        display: flex;
        justify-content: flex-start;
    }

    .media-text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: rgb(55 65 81);
    }

    .media-text-sm:where(.dark, .dark *) {
        color: rgb(209 213 219);
    }

    .media-timestamp {
        color: rgb(107 114 128);
        font-size: 0.75rem;
        line-height: 1rem;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .media-timestamp:where(.dark, .dark *) {
        color: rgb(156 163 175);
    }

    .media-image-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .media-modal-content {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        width: 100%;
        height: 100%;
    }

    .media-preview-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        min-height: 400px;
        border: 1px solid rgb(229 231 235);
        border-radius: 0.5rem;
        background-color: rgb(255 255 255);
        overflow: hidden;
        cursor: zoom-in;
        transition: background-color 0.15s ease;
    }

    .media-preview-link:where(.dark, .dark *) {
        border-color: rgb(55 65 81);
        background-color: rgb(17 24 39);
    }

    .media-preview-link:hover {
        background-color: rgb(249 250 251);
    }

    .media-preview-link:hover:where(.dark, .dark *) {
        background-color: rgb(55 65 81 / 0.5);
    }

    .media-preview-link img {
        max-width: 100%;
        max-height: 70vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 0.375rem;
    }

    .media-video-full {
        width: 100%;
        max-height: 70vh;
        border-radius: 0.375rem;
    }

    .media-file-preview-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .media-file-preview-icon-large {
        width: 8rem;
        height: 8rem;
    }

    .fullscreen-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background-color: rgba(0, 0, 0, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .fullscreen-close-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: white;
        background: rgba(0, 0, 0, 0.75);
        border-radius: 0.5rem;
        padding: 0.75rem;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.2s ease;
    }

    .fullscreen-close-btn:hover {
        background: rgba(0, 0, 0, 0.9);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .meta-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 1rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background-color: rgb(243 244 246);
        border-radius: 0.5rem;
        border: 1px solid rgb(229 231 235);
    }

    .meta-section:where(.dark, .dark *) {
        background-color: rgb(31 41 55);
        border-color: rgb(55 65 81);
    }

    .empty-state-container {
        padding: 3rem 1.5rem;
    }

    .empty-state-content {
        margin-left: auto;
        margin-right: auto;
        display: grid;
        max-width: 32rem;
        justify-items: center;
        text-align: center;
    }

    .empty-state-icon-container {
        margin-bottom: 1rem;
        border-radius: 9999px;
        background-color: rgb(229 231 235);
        padding: 0.75rem;
    }

    .empty-state-icon-container:where(.dark, .dark *) {
        background-color: rgb(107 114 128 / 0.2);
    }

    .empty-state-icon {
        height: 1.5rem;
        width: 1.5rem;
        color: rgb(75 85 99);
    }

    .empty-state-icon:where(.dark, .dark *) {
        color: rgb(156 163 175);
    }

    .empty-state-heading {
        font-size: 1.125rem;
        line-height: 1.75rem;
        font-weight: 600;
        color: rgb(17 24 39);
        margin: 0;
    }

    .empty-state-heading:where(.dark, .dark *) {
        color: rgb(249 250 251);
    }
</style>

@if(isset($records) || count($folders) > 0)
<div class="media-grid">
    @if(isset($records))
        @foreach($records as $item)
            @if($item instanceof \TomatoPHP\FilamentMediaManager\Models\Folder)
                @livewire(\TomatoPHP\FilamentMediaManager\Livewire\FolderComponent::class, ['item' => $item], key('folder-' . $item->id))
            @else
                <x-filament::modal  width="3xl" slide-over>
                <x-slot name="trigger" class="media-trigger-slot">
                    <div class="media-card">
                        @php
                            $extension = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
                            $title = $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name;

                            // File type icon mapping
                            $fileIcons = [
                                // Documents
                                'pdf' => ['icon' => 'heroicon-o-document-text', 'color' => '#ef4444'],
                                'doc' => ['icon' => 'heroicon-o-document-text', 'color' => '#3b82f6'],
                                'docx' => ['icon' => 'heroicon-o-document-text', 'color' => '#3b82f6'],
                                'odt' => ['icon' => 'heroicon-o-document-text', 'color' => '#3b82f6'],
                                'rtf' => ['icon' => 'heroicon-o-document-text', 'color' => '#6b7280'],
                                'txt' => ['icon' => 'heroicon-o-document', 'color' => '#6b7280'],
                                'md' => ['icon' => 'heroicon-o-document', 'color' => '#6b7280'],

                                // Spreadsheets
                                'xls' => ['icon' => 'heroicon-o-table-cells', 'color' => '#22c55e'],
                                'xlsx' => ['icon' => 'heroicon-o-table-cells', 'color' => '#22c55e'],
                                'ods' => ['icon' => 'heroicon-o-table-cells', 'color' => '#22c55e'],
                                'csv' => ['icon' => 'heroicon-o-table-cells', 'color' => '#10b981'],

                                // Presentations
                                'ppt' => ['icon' => 'heroicon-o-presentation-chart-bar', 'color' => '#f97316'],
                                'pptx' => ['icon' => 'heroicon-o-presentation-chart-bar', 'color' => '#f97316'],
                                'odp' => ['icon' => 'heroicon-o-presentation-chart-bar', 'color' => '#f97316'],

                                // Archives
                                'zip' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                'rar' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                '7z' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                'tar' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],
                                'gz' => ['icon' => 'heroicon-o-archive-box', 'color' => '#eab308'],

                                // Code files
                                'json' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#eab308'],
                                'xml' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#8b5cf6'],
                                'html' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#f97316'],
                                'css' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                'js' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#eab308'],
                                'ts' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#3b82f6'],
                                'jsx' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                'tsx' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                'php' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#8b5cf6'],
                                'py' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#3b82f6'],
                                'rb' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#ef4444'],
                                'java' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#f97316'],
                                'cpp' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                'c' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#3b82f6'],
                                'go' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#06b6d4'],
                                'rust' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#f97316'],
                                'swift' => ['icon' => 'heroicon-o-code-bracket', 'color' => '#f97316'],

                                // Images (vector)
                                'svg' => ['icon' => 'heroicon-o-photo', 'color' => '#f59e0b'],
                                'eps' => ['icon' => 'heroicon-o-photo', 'color' => '#8b5cf6'],
                                'ai' => ['icon' => 'heroicon-o-photo', 'color' => '#f97316'],

                                // Others
                                'psd' => ['icon' => 'heroicon-o-photo', 'color' => '#3b82f6'],
                                'sketch' => ['icon' => 'heroicon-o-photo', 'color' => '#f59e0b'],
                                'fig' => ['icon' => 'heroicon-o-photo', 'color' => '#ec4899'],
                            ];

                            $fileIcon = $fileIcons[$extension] ?? ['icon' => 'heroicon-o-document', 'color' => '#9ca3af'];
                        @endphp

                        <div class="media-preview {{ str($item->mime_type)->contains(['image', 'video']) ? 'media-preview-with-bg' : '' }}"
                             x-data>
                            @if(str($item->mime_type)->contains('image'))
                                <img src="{{ $item->getUrl() }}" alt="{{ $title }}" />
                            @elseif(str($item->mime_type)->contains('video'))
                                <video src="{{ $item->getUrl() }}"></video>
                            @elseif(str($item->mime_type)->contains('audio'))
                                <x-icon name="heroicon-o-musical-note" class="media-icon-large" style="color: #ec4899;" />
                            @else
                                @php
                                    $hasPreview = false;
                                    $loadTypes = \TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
                                    $type = null;
                                    foreach ($loadTypes as $getType) {
                                        if(str($item->file_name)->contains($getType->exstantion)){
                                            $hasPreview = $getType->preview;
                                            $type = $getType;
                                        }
                                    }
                                @endphp
                                @if($hasPreview && $type)
                                    <x-icon :name="$type->icon" class="media-icon-large" style="color: {{ $fileIcon['color'] ?? '#9ca3af' }};" />
                                @else
                                    <div class="media-file-icon">
                                        <x-icon :name="$fileIcon['icon']" class="media-file-icon-image" style="color: {{ $fileIcon['color'] }};" />
                                        <span class="media-file-extension">{{ $extension }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="media-info">
                            <h1 class="media-title"
                                x-data
                                x-tooltip="{
                                    content: '{{ addslashes($title) }}',
                                    theme: $root.closest('.dark') ? 'dark' : 'light',
                                }">
                                {{ $title }}
                            </h1>
                            <p class="media-timestamp">
                                {{ $item->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="heading">
                    {{ $item->uuid }}
                </x-slot>

                <x-slot name="description">
                    {{ $item->file_name }}
                </x-slot>

                <div>
                    <div class="media-modal-content">

                        @if(str($item->mime_type)->contains('image'))
                            <div class="media-preview-link" x-data="{ fullscreen: false }">
                                <img src="{{ $item->getUrl() }}" alt="{{ $title }}" @click="fullscreen = true" style="cursor: zoom-in;" />

                                <!-- Fullscreen Image Modal -->
                                <div x-show="fullscreen"
                                     x-transition.opacity
                                     @click="fullscreen = false"
                                     @keydown.escape.window="fullscreen = false"
                                     class="fullscreen-modal"
                                     x-cloak
                                     style="display: none;"
                                     x-bind:style="fullscreen && 'display: flex !important;'">
                                    <img src="{{ $item->getUrl() }}"
                                         alt="{{ $title }}"
                                         style="max-width: 95%; max-height: 95vh; object-fit: contain; cursor: zoom-out;"
                                         @click.stop />
                                    <button @click="fullscreen = false" class="fullscreen-close-btn">
                                        <x-icon name="heroicon-o-x-mark" style="width: 1.5rem; height: 1.5rem;" />
                                    </button>
                                </div>
                            </div>

                        @elseif(str($item->mime_type)->contains('video'))
                            <div class="media-preview-link">
                                <video class="media-video-full" controls>
                                    <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                </video>
                            </div>

                        @elseif(str($item->mime_type)->contains('audio'))
                            <div class="media-preview-link">
                                <div class="media-file-preview-icon">
                                    <x-icon name="heroicon-o-musical-note" class="media-file-preview-icon-large" style="color: #ec4899;" />
                                </div>
                                <audio class="media-video-full" controls style="width: 100%; margin-top: 1rem;">
                                    <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                </audio>
                            </div>
                        @else
                            @php
                                $hasPreview = false;
                                $loadTypes = \TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
                                $previewType = null;
                                foreach ($loadTypes as $type) {
                                    if(str($item->file_name)->contains($type->exstantion)){
                                        $hasPreview = $type->preview;
                                        $previewType = $type;
                                    }
                                }
                            @endphp
                            @if($hasPreview)
                                @include($hasPreview, ['media' => $item])
                            @else
                                <a href="{{ $item->getUrl() }}" target="_blank" class="media-preview-link">
                                    <div class="media-file-preview-icon">
                                        <x-icon :name="$fileIcon['icon']" class="media-file-preview-icon-large" style="color: {{ $fileIcon['color'] }};" />
                                        <span style="font-size: 1rem; font-weight: 600; text-transform: uppercase; color: {{ $fileIcon['color'] }};">{{ $extension }}</span>
                                        <span style="font-size: 0.875rem; color: rgb(107 114 128); margin-top: 0.5rem;">Click to download</span>
                                    </div>
                                </a>
                            @endif
                        @endif
                        <div class="meta-section">
                            @if($item->model)
                            <div>
                                <div>
                                    <h1 class="media-subtitle">{{ trans('filament-media-manager::messages.media.meta.model') }}</h1>
                                </div>
                                <div class="flex-start">
                                    <p class="media-text-sm">
                                      {{str($item->model_type)->afterLast('\\')->title()}}[ID:{{ $item->model?->id }}]
                                    </p>
                                </div>
                            </div>
                            @endif
                            <div>
                                <div>
                                    <h1 class="media-subtitle">{{ trans('filament-media-manager::messages.media.meta.file-name') }}</h1>
                                </div>
                                <div class="flex-start">
                                    <p class="media-text-sm">
                                        {{ $item->file_name }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="media-subtitle">{{ trans('filament-media-manager::messages.media.meta.type') }}</h1>
                                </div>
                                <div class="flex-start">
                                    <p class="media-text-sm">
                                        {{ $item->mime_type }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="media-subtitle">{{ trans('filament-media-manager::messages.media.meta.size') }}</h1>
                                </div>
                                <div class="flex-start">
                                    <p class="media-text-sm">
                                        {{ $item->humanReadableSize }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="media-subtitle">{{ trans('filament-media-manager::messages.media.meta.disk') }}</h1>
                                </div>
                                <div class="flex-start">
                                    <p class="media-text-sm">
                                        {{ $item->disk  }}
                                    </p>
                                </div>
                            </div>
                            @if($item->custom_properties)
                                @foreach($item->custom_properties as $key=>$value)
                                    @if($value)
                                        <div>
                                            <div>
                                                <h1 class="media-subtitle">{{str($key)->title()}}</h1>
                                            </div>
                                            <div class="flex-start">
                                                <p class="media-text-sm">
                                                    {{ $value }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                    @if(filament('filament-media-manager')->allowUserAccess && (!empty($currentFolder->user_id)))
                        @if($currentFolder->user_id === auth()->user()->id && $currentFolder->user_type === get_class(auth()->user()))
                            <x-slot name="footer">
                                {{ ($this->editMedia)(['record' => $item]) }}
                                {{ ($this->deleteMedia)(['record' => $item]) }}
                            </x-slot>
                        @endif
                    @else
                        <x-slot name="footer">
                            {{ ($this->editMedia)(['record' => $item]) }}
                            {{ ($this->deleteMedia)(['record' => $item]) }}
                        </x-slot>
                    @endif

            </x-filament::modal>
            @endif
        @endforeach
    @endif
    @if(filament('filament-media-manager')->allowSubFolders)
        @foreach($folders as $folder)
            @livewire(\TomatoPHP\FilamentMediaManager\Livewire\FolderComponent::class, ['item' => $folder], key('folder-' . $folder->id))
        @endforeach
    @endif
</div>
@else
    <div class="empty-state-container">
        <div class="empty-state-content">
            <div class="empty-state-icon-container">
                <x-filament::icon
                    icon="heroicon-o-x-mark"
                    class="empty-state-icon"
                />
            </div>

            <h3 class="empty-state-heading">
                {{ trans('filament-media-manager::messages.empty.title') }}
            </h3>
        </div>
    </div>
@endif
