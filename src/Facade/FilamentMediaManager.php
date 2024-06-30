<?php

namespace TomatoPHP\FilamentMediaManager\Facade;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static void register(MediaManagerType|array $type)
 * @method static array getTypes()
 */
class FilamentMediaManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-media-manager';
    }
}
