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

@if(isset($records) || count($folders) > 0)
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
    @if(isset($records))
        @foreach($records as $item)
            @if($item instanceof \TomatoPHP\FilamentMediaManager\Models\Folder)
                {{ ($this->folderAction($item))(['record' => $item]) }}
            @else
                <x-filament::modal  width="3xl" slide-over>
                <x-slot name="trigger" class="w-full h-full">
                    <div class="flex flex-col justify-start gap-4 border dark:border-gray-700 rounded-lg shadow-sm p-2 w-full h-full">
                        <div class="flex flex-col items-center justify-center  p-4 h-full">
                            @if(str($item->mime_type)->contains('image'))
                                <img src="{{ $item->getUrl() }}" />
                            @elseif(str($item->mime_type)->contains('video'))
                                <video src="{{ $item->getUrl() }}"></video>
                            @elseif(str($item->mime_type)->contains('audio'))
                                <x-icon name="heroicon-o-musical-note" class="w-32 h-32" />
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
                                    <x-icon :name="$type->icon" class="w-32 h-32" />
                                @else
                                    <x-icon name="heroicon-o-document" class="w-32 h-32" />
                                @endif
                            @endif
                        </div>
                        <div>
                            <div class="flex flex-col justify-between border-t dark:border-gray-700 p-4">
                                <div>
                                    <h1 class="font-bold break-words">{{ $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name }}</h1>
                                </div>

                                @if($item->hasCustomProperty('description') && !empty($item->getCustomProperty('description')))
                                    <div>
                                        <div>
                                            <h1 class="font-bold">Description</h1>
                                        </div>
                                        <div class="flex justify-start">
                                            <p class="text-sm">
                                                {{ $item->getCustomProperty('description') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif



                                <div class="flex justify-start">
                                    <p class="text-gray-600 dark:text-gray-300 text-sm truncate ...">
                                        {{ $item->created_at->diffForHumans() }}
                                    </p>
                                </div>

                            </div>
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
                    <div class="flex flex-col justify-start w-full h-full">

                        @if(str($item->mime_type)->contains('image'))
                            <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center  p-4 h-full border dark:border-gray-700 rounded-lg">
                                <img src="{{ $item->getUrl() }}" />
                            </a>

                        @elseif(str($item->mime_type)->contains('video'))
                            <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center  p-4 h-full border dark:border-gray-700 rounded-lg">
                                <video class="w-full h-full" controls>
                                    <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                </video>
                            </a>

                        @elseif(str($item->mime_type)->contains('audio'))
                            <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center  p-4 h-full border dark:border-gray-700 rounded-lg">
                                <video class="w-full h-full" controls>
                                    <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                </video>
                            </a>
                        @else
                            @php
                                $hasPreview = false;
                                $loadTypes = \TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
                                foreach ($loadTypes as $type) {
                                    if(str($item->file_name)->contains($type->exstantion)){
                                        $hasPreview = $type->preview;
                                    }
                                }
                            @endphp
                            @if($hasPreview)
                                @include($hasPreview, ['media' => $item])

                            @else
                                <a href="{{ $item->getUrl() }}" target="_blank" class="flex flex-col items-center justify-center  p-4 h-full border dark:border-gray-700 rounded-lg">
                                    @if($type)
                                        <x-icon :name="$type->icon" class="w-32 h-32" />
                                    @else
                                        <x-icon name="heroicon-o-document" class="w-32 h-32" />
                                    @endif
                                </a>
                            @endif
                        @endif
                        <div class="flex flex-col gap-4 my-4">
                            @if($item->model)
                            <div>
                                <div>
                                    <h1 class="font-bold">{{ trans('filament-media-manager::messages.media.meta.model') }}</h1>
                                </div>
                                <div class="flex justify-start">
                                    <p class="text-sm">
                                      {{str($item->model_type)->afterLast('\\')->title()}}[ID:{{ $item->model?->id }}]
                                    </p>
                                </div>
                            </div>
                            @endif
                            <div>
                                <div>
                                    <h1 class="font-bold">{{ trans('filament-media-manager::messages.media.meta.file-name') }}</h1>
                                </div>
                                <div class="flex justify-start">
                                    <p class="text-sm">
                                        {{ $item->file_name }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="font-bold">{{ trans('filament-media-manager::messages.media.meta.type') }}</h1>
                                </div>
                                <div class="flex justify-start">
                                    <p class="text-sm">
                                        {{ $item->mime_type }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="font-bold">{{ trans('filament-media-manager::messages.media.meta.size') }}</h1>
                                </div>
                                <div class="flex justify-start">
                                    <p class="text-sm">
                                        {{ $item->humanReadableSize }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <h1 class="font-bold">{{ trans('filament-media-manager::messages.media.meta.disk') }}</h1>
                                </div>
                                <div class="flex justify-start">
                                    <p class="text-sm">
                                        {{ $item->disk  }}
                                    </p>
                                </div>
                            </div>
                            @if($item->custom_properties)
                                @foreach($item->custom_properties as $key=>$value)
                                    @if($value)
                                        <div>
                                            <div>
                                                <h1 class="font-bold">{{str($key)->title()}}</h1>
                                            </div>
                                            <div class="flex justify-start">
                                                <p class="text-sm">
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
                                {{ ($this->deleteMedia)(['record' => $item]) }}
                            </x-slot>
                        @endif
                    @else
                        <x-slot name="footer">
                            {{ ($this->deleteMedia)(['record' => $item]) }}
                        </x-slot>
                    @endif

            </x-filament::modal>
            @endif
        @endforeach
    @endif
    @if(filament('filament-media-manager')->allowSubFolders)
        @foreach($folders as $folder)
            {{ ($this->folderAction($folder))(['record' => $folder]) }}
        @endforeach
    @endif
</div>
@else
    <div
        class="fi-ta-empty-state px-6 py-12"
    >
        <div
            class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center"
        >
            <div
                class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20"
            >
                <x-filament::icon
                    icon="heroicon-o-x-mark"
                    class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                />
            </div>

            <x-filament-tables::empty-state.heading>
                {{ trans('filament-media-manager::messages.empty.title') }}
            </x-filament-tables::empty-state.heading>
        </div>
    </div>
@endif
