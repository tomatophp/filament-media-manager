<x-filament-actions::action
    :action="$action"
    :badge="$getBadge()"
    :badge-color="$getBadgeColor()"
    dynamic-component="filament::button"
    :label="$getLabel()"
    :size="$getSize()"
    class="fi-ac-icon-btn-action"
    color="gray"
>
    <style>
        .folder-icon-{{$item->id}} {
            width: 100px;
            height: 70px;
            background-color: {{$item->color?? '#f3c623'}};
            border-radius: 5px;
            position: relative;
            margin-top: 20px;
            margin-right: 10px;
            margin-left: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .folder-icon-{{$item->id}}::before {
            content: "";
            width: 40px;
            height: 10px;
            background-color: {{$item->color?? '#f3c623'}};
            border-radius: 5px 5px 0 0;
            position: absolute;
            top: -10px;
            left: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
    <div class="flex flex-col justify-center items-center gap-4">
        <div class="folder-icon-{{$item->id}} flex flex-col items-center justify-center">
            @if($item->icon)
                <x-icon name="{{$item->icon}}" class="text-white w-8 h-8"/>
            @endif
        </div>
        <div class="flex flex-col items-center justify-center my-2">
            <div>
                <h1 class="font-bold text-xl">{{ $item->name }}</h1>
            </div>

            <div class="flex justify-start mt-1">
                <p class="text-gray-600 dark:text-gray-300 text-sm truncate ...">
                    {{ $item->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
</x-filament-actions::action>

