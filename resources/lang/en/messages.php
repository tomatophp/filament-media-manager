<?php

return [
    'empty' => [
        'title' => 'No Media or Folders Found',
    ],
    'folders' => [
        'title' => 'Media Manager',
        'single' => 'Folder',
        'columns' => [
            'name' => 'Name',
            'collection' => 'Collection',
            'description' => 'Description',
            'is_public' => 'Is Public',
            'has_user_access' => 'Has User Access',
            'users' => 'Users',
            'icon' => 'Icon',
            'color' => 'Color',
            'is_protected' => 'Is Protected',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
        ],
        'filters' => [
            'all_folders' => 'All folders',
            'protected_only' => 'Protected only',
            'public_only' => 'Public only',
            'created_from' => 'Created from',
            'created_until' => 'Created until',
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
            'size' => 'Size',
            'order_column' => 'Order Column',
        ],
        'filters' => [
            'size_from' => 'Size from (KB)',
            'size_to' => 'Size to (KB)',
            'created_from' => 'Created from',
            'created_until' => 'Created until',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => 'Create Sub Folder',
            ],
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
        'notifications' => [
            'create-media' => 'Media created successfully',
            'delete-folder' => 'Folder deleted successfully',
            'edit-folder' => 'Folder edited successfully',
        ],
        'meta' => [
            'model' => 'Model',
            'file-name' => 'File Name',
            'type' => 'Type',
            'size' => 'Size',
            'disk' => 'Disk',
            'url' => 'URL',
            'edit-media' => 'Edit Media',
            'delete-media' => 'Delete Media',
        ],
    ],
    'picker' => [
        'title' => 'Select Media',
        'browse' => 'Browse Media',
        'remove' => 'Remove',
        'select' => 'Select',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'search' => 'Search folders and files...',
        'select_folder' => 'Select a folder to browse media files',
        'folders' => 'Folders',
        'media_files' => 'Media Files',
        'empty' => 'No folders or media files found',
        'no_media_selected' => 'No media selected',
        'selected' => 'selected',
        'clear_all' => 'Clear all',
        'confirm_remove' => 'Remove Media',
        'confirm_remove_message' => 'Are you sure you want to remove this media item?',
    ],
];
