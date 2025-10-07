@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\Size;
    use Filament\Support\View\Components\BadgeComponent;
    use Filament\Support\View\Components\ButtonComponent;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'badge' => null,
    'badgeColor' => 'primary',
    'badgeSize' => Size::ExtraSmall,
    'color' => 'primary',
    'disabled' => false,
    'form' => null,
    'formId' => null,
    'href' => null,
    'icon' => null,
    'iconAlias' => null,
    'iconPosition' => IconPosition::Before,
    'iconSize' => null,
    'keyBindings' => null,
    'labeledFrom' => null,
    'labelSrOnly' => false,
    'loadingIndicator' => true,
    'outlined' => false,
    'size' => Size::Medium,
    'spaMode' => null,
    'tag' => 'button',
    'target' => null,
    'tooltip' => null,
    'type' => 'button',
])


<button
    wire:click="mountAction('getFolderAction', { item: {{$item}} })"
    style="background: transparent; border: none; cursor: pointer; padding: 0;"
>
    <style>
        .folder-container-{{$item->id}} {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.15s ease;
        }

        .folder-container-{{$item->id}}:hover {
            background-color: rgb(249 250 251);
        }

        .folder-container-{{$item->id}}:hover:where(.dark, .dark *) {
            background-color: rgb(55 65 81 / 0.5);
        }

        .folder-icon-{{$item->id}} {
            width: 100px;
            height: 70px;
            background-color: {{$item->color?? '#f3c623'}};
            border-radius: 5px;
            position: relative;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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

        .folder-icon-content-{{$item->id}} {
            color: white;
            width: 2rem;
            height: 2rem;
        }

        .folder-lock-badge-{{$item->id}} {
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

        .folder-lock-badge-{{$item->id}}:where(.dark, .dark *) {
            background-color: rgb(220 38 38);
        }

        .folder-lock-icon-{{$item->id}} {
            width: 1rem;
            height: 1rem;
            color: white;
        }

        .folder-info-{{$item->id}} {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .folder-name-{{$item->id}} {
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

        .folder-name-{{$item->id}}:where(.dark, .dark *) {
            color: rgb(249 250 251);
        }

        .folder-date-{{$item->id}} {
            color: rgb(107 114 128);
            font-size: 0.75rem;
            line-height: 1rem;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .folder-date-{{$item->id}}:where(.dark, .dark *) {
            color: rgb(156 163 175);
        }
    </style>
    <div class="folder-container-{{$item->id}}">
        <div class="folder-icon-{{$item->id}}">
            @if($item->icon)
                <x-icon name="{{$item->icon}}" class="folder-icon-content-{{$item->id}}"/>
            @endif
            @if($item->is_protected)
                <div class="folder-lock-badge-{{$item->id}}">
                    <x-icon name="heroicon-o-lock-closed" class="folder-lock-icon-{{$item->id}}" />
                </div>
            @endif
        </div>
        <div class="folder-info-{{$item->id}}">
            <h1 class="folder-name-{{$item->id}}"
                x-data
                x-tooltip="{
                    content: '{{ addslashes($item->name) }}',
                    theme: $root.closest('.dark') ? 'dark' : 'light',
                }">
                {{ $item->name }}
            </h1>
            <p class="folder-date-{{$item->id}}">
                {{ $item->created_at->diffForHumans() }}
            </p>
        </div>
    </div>
</button>
