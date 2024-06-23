<?php

return [
    'folders' => [
        'title' => 'Media Manager',
        'single' => 'Folder',
        'columns' => [
            'name' => 'Name',
            'collection' => 'Collection',
            'description' => 'Description',
            'icon' => 'Icon',
            'color' => 'Color',
            'is_protected' => 'Is Protected',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
        ],
        'group' => 'Content',
    ],
    'media' => [
        'title' => 'Media',
        'single' => 'Media',
        'columns' => [
            'image' => 'Image',
            'model' => 'Model',
            'collection_name' => 'Collection Name',
        ],
        'actions' => [
            'create' => [
                'label' => 'Add Media',
                'form' => [
                    'file' => 'File',
                    'title' => 'Title',
                    'description' => 'Description',
                ],
            ],
            'delete' => [
                'label' => 'Delete Folder',
            ],
            'edit' => [
                'label' => 'Edit Folder',
            ],
        ],
        'notificaitons' => [
            'create-media' => 'Media created successfully',
            'delete-folder' => 'Folder deleted successfully',
            'edit-folder' => 'Folder edited successfully',
        ],
    ],
];
