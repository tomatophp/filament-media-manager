
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
    @foreach($records as $item)

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
                                foreach ($loadTypes as $type) {
                                    if(str($item->file_name)->contains($type->exstantion)){
                                        $hasPreview = $type->preview;
                                    }
                                }
                            @endphp
                            @if($hasPreview)
                                <x-icon :name="$type->icon" class="w-32 h-32" />
                            @else
                                <x-icon name="heroicon-o-document" class="w-32 h-32" />
                            @endif
                        @endif
                    </div>
                    <div>
                        <div class="flex flex-col justify-between border-t dark:border-gray-700 p-4">
                            <div>
                                <h1 class="font-bold">{{ $item->hasCustomProperty('title') ? $item->getCustomProperty('title') : $item->name }}</h1>
                            </div>

                            @if($item->hasCustomProperty('description'))
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
                                <x-icon :name="$type->icon" class="w-32 h-32" />
                            </a>
                        @endif
                    @endif
                    <div class="flex flex-col gap-4 my-4">
                        @if($item->model)
                        <div>
                            <div>
                                <h1 class="font-bold">Model</h1>
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
                                <h1 class="font-bold">File Name</h1>
                            </div>
                            <div class="flex justify-start">
                                <p class="text-sm">
                                    {{ $item->file_name }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <div>
                                <h1 class="font-bold">Type</h1>
                            </div>
                            <div class="flex justify-start">
                                <p class="text-sm">
                                    {{ $item->mime_type }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <div>
                                <h1 class="font-bold">Size</h1>
                            </div>
                            <div class="flex justify-start">
                                <p class="text-sm">
                                    {{ ($item->size/1000) .'KB' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <div>
                                <h1 class="font-bold">Disk</h1>
                            </div>
                            <div class="flex justify-start">
                                <p class="text-sm">
                                    {{ $item->disk  }}
                                </p>
                            </div>
                        </div>
                        @if($item->custom_properties)
                            @foreach($item->custom_properties as $key=>$value)
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
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <x-slot name="footer">

            </x-slot>
        </x-filament::modal>
    @endforeach
</div>
