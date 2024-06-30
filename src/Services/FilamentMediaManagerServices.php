<?php

namespace TomatoPHP\FilamentMediaManager\Services;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use TomatoPHP\FilamentMediaManager\Services\Contracts\MediaManagerType;

class FilamentMediaManagerServices
{
    protected array $types = [];

    public function register(MediaManagerType|array $type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                $this->register($t);
            }
        } else {
            if($type->js){
                FilamentAsset::register([
                    Js::make($type->exstantion.'_js', $type->js),
                ]);
            }
            if($type->css){
                FilamentAsset::register([
                    Css::make($type->exstantion.'_css', $type->css),
                ]);
            }

            $this->types[] = $type;
        }
    }

    public function getTypes()
    {
        return $this->types;
    }
}
