<?php

use App\Models\User;
use TomatoPHP\FilamentMediaManager\Http\Resources\FolderResource;
use TomatoPHP\FilamentMediaManager\Http\Resources\FoldersResource;
use TomatoPHP\FilamentMediaManager\Http\Resources\MediaResource;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Models\Media;

return [
    'model' => [
        'folder' => Folder::class,
        'media' => Media::class,
    ],

    'api' => [
        'active' => false,
        'middlewares' => [
            'api',
            'auth:sanctum',
        ],
        'prefix' => 'api/media-manager',
        'resources' => [
            'folders' => FoldersResource::class,
            'folder' => FolderResource::class,
            'media' => MediaResource::class,
        ],
    ],

    'user' => [
        'model' => User::class, // Change this to your user model
        'column_name' => 'name', // Change the value if your field in users table is different from "name"
        'id_column' => 'id', // Change the value if your users table uses a different key column
    ],

    'navigation_sort' => 0,
];
