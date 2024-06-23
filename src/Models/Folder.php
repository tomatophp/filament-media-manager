<?php

namespace TomatoPHP\FilamentMediaManager\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Folder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'model_type',
        'model_id',
        'name',
        'collection',
        'description',
        'icon',
        'color',
        'is_protected',
        'password',
        'is_hidden',
        'is_favorite',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_hidden' => 'boolean',
        'is_favorite' => 'boolean'
    ];

    public function model()
    {
        return $this->morphTo();
    }

}
