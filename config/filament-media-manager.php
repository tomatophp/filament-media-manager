<?php

return [
    /**
     * --------------------------------------------------------------------------
     * The model classes for the folder and media.
     * --------------------------------------------------------------------------
     */
    'model' => [
        'folder' => \TomatoPHP\FilamentMediaManager\Models\Folder::class,
        'media' => \TomatoPHP\FilamentMediaManager\Models\Media::class,
        'user' => \App\Models\User::class,
    ],

    /**
     * --------------------------------------------------------------------------
     * The disk to store the media.
     * --------------------------------------------------------------------------
     */
    'disk' => 'public',

    /**
     * --------------------------------------------------------------------------
     * Use the api routes.
     * --------------------------------------------------------------------------
     */
    'api' => [
        'active' => false,
        'middlewares' => [
            'api',
            'auth:sanctum',
        ],
        'prefix' => 'api/media-manager',
        'resources' => [
            'folders' => \TomatoPHP\FilamentMediaManager\Http\Resources\FoldersResource::class,
            'folder' => \TomatoPHP\FilamentMediaManager\Http\Resources\FolderResource::class,
            'media' => \TomatoPHP\FilamentMediaManager\Http\Resources\MediaResource::class,
        ],
    ],

    /**
     * --------------------------------------------------------------------------
     * The user column name.
     * --------------------------------------------------------------------------
     * Change the value if your field in users table is different from "name"
     */
    'user' => [
        'column_name' => 'name',
    ],
];
