@php
    $extension = strtolower(pathinfo($getRecord()->file_name, PATHINFO_EXTENSION));

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

<style>
    .table-media-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 70px;
        position: relative;
        overflow: hidden;
        margin: auto;
    }

    .table-media-preview-with-bg {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        background-color: rgb(243 244 246);
    }

    .table-media-preview-with-bg:where(.dark, .dark *) {
        background-color: rgb(31 41 55);
    }

    .table-media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .table-media-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .table-media-icon-large {
        width: 4rem;
        height: 4rem;
    }

    .table-media-file-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        width: 100%;
        height: 100%;
    }

    .table-media-file-icon-image {
        width: 3.5rem;
        height: 3.5rem;
    }

    .table-media-file-extension {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        color: rgb(107 114 128);
    }

    .table-media-file-extension:where(.dark, .dark *) {
        color: rgb(156 163 175);
    }
</style>

<div class="table-media-preview {{ str($getRecord()->mime_type)->contains(['image', 'video']) ? 'table-media-preview-with-bg' : '' }}">
    @if(str($getRecord()->mime_type)->contains('image'))
        <img src="{{ $getRecord()->getUrl() }}" alt="{{ $getRecord()->name }}" />
    @elseif(str($getRecord()->mime_type)->contains('video'))
        <video src="{{ $getRecord()->getUrl() }}"></video>
    @elseif(str($getRecord()->mime_type)->contains('audio'))
        <x-icon name="heroicon-o-musical-note" class="table-media-icon-large" style="color: #ec4899;" />
    @else
        @php
            $hasPreview = false;
            $loadTypes = \TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
            $type = null;
            foreach ($loadTypes as $getType) {
                if(str($getRecord()->file_name)->contains($getType->exstantion)){
                    $hasPreview = $getType->preview;
                    $type = $getType;
                }
            }
        @endphp
        @if($hasPreview && $type)
            <x-icon :name="$type->icon" class="table-media-icon-large" style="color: {{ $fileIcon['color'] ?? '#9ca3af' }};" />
        @else
            <div class="table-media-file-icon">
                <x-icon :name="$fileIcon['icon']" class="table-media-file-icon-image" style="color: {{ $fileIcon['color'] }};" />
                <span class="table-media-file-extension">{{ $extension }}</span>
            </div>
        @endif
    @endif
</div>
