<?php

namespace TomatoPHP\FilamentMediaManager\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use TomatoPHP\FilamentMediaManager\Tests\Database\Factories\ProductFactory;
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaManager;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use InteractsWithMediaManager;

    protected $guarded = [];

    protected $table = 'products';

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
