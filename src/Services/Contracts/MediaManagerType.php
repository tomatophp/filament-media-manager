<?php

namespace TomatoPHP\FilamentMediaManager\Services\Contracts;

class MediaManagerType
{
    public ?string $exstantion = null;
    public ?string $icon = null;
    public ?string $preview = null;
    public ?string $js = null;
    public ?string $css = null;


    public static function make(?string $exstantion=null): static
    {
        return (new static())->exstantion($exstantion);
    }

    public function exstantion(?string $exstantion=null): static
    {
        $this->exstantion = $exstantion;
        return $this;
    }

    public function icon(?string $icon=null): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function preview(?string $preview=null): static
    {
        $this->preview = $preview;
        return $this;
    }

    public function js(?string $js=null): static
    {
        $this->js = $js;
        return $this;
    }

    public function css(?string $css=null): static
    {
        $this->css = $css;
        return $this;
    }
}
