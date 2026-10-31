<?php

namespace TomatoPHP\FilamentMediaManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Livewire\Livewire;
use TomatoPHP\FilamentMediaManager\Livewire\MediaPicker;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;

class FilamentMediaManagerPlugin implements Plugin
{
    public ?bool $allowSubFolders = false;

    public ?bool $allowUserAccess = false;

    public ?string $navigationLabel = null;

    public ?string $navigationGroup = null;

    public ?string $navigationIcon = null;

    public const DEFAULT_FOLDER_COLOR = '#f3c623';

    public ?string $defaultFolderColor = null;

    /**
     * @var array<string>
     */
    public array $folderColors = [];

    public function getId(): string
    {
        return 'filament-media-manager';
    }

    public function allowSubFolders(bool $condation = true): static
    {
        $this->allowSubFolders = $condation;

        return $this;
    }

    public function allowUserAccess(bool $condation = true): static
    {
        $this->allowUserAccess = $condation;

        return $this;
    }

    /**
     * The color of new folders, and of folders without one
     */
    public function defaultFolderColor(?string $color): static
    {
        $this->defaultFolderColor = $color;

        return $this;
    }

    /**
     * Colours offered in the folder form, next to a free color picker
     *
     * @param  array<string>  $colors
     */
    public function folderColors(array $colors): static
    {
        $this->folderColors = array_values($colors);

        return $this;
    }

    /**
     * The default color of the plugin in the current panel; the picker can also be used in a panel without it
     */
    public static function resolveDefaultFolderColor(): string
    {
        return static::current()->defaultFolderColor ?? static::DEFAULT_FOLDER_COLOR;
    }

    /**
     * @return array<string>
     */
    public static function resolveFolderColors(): array
    {
        return static::current()->folderColors ?? [];
    }

    protected static function current(): ?self
    {
        $panel = filament()->getCurrentOrDefaultPanel();

        if (! $panel?->hasPlugin('filament-media-manager')) {
            return null;
        }

        $plugin = $panel->getPlugin('filament-media-manager');

        return $plugin instanceof self ? $plugin : null;
    }

    public function navigationLabel(string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FolderResource::class,
            MediaResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        Livewire::component('media-picker', MediaPicker::class);
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
