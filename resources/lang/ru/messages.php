<?php

return [
    'empty' => [
        'title' => "Медиа или папки отсутствуют",
    ],
    'folders' => [
        'title' => 'Медиа менеджер',
        'single' => 'Папка',
        'columns' => [
            'name' => 'Название',
            'collection' => 'Коллекция',
            'description' => 'Описание',
            'is_public' => 'Публичный',
            'has_user_access' => 'Есть доступ пользователя',
            'users' => 'Пользователи',
            'icon' => 'Иконка',
            'color' => 'Цвет',
            'is_protected' => 'Защищено',
            'password' => 'Пароль',
            'password_confirmation' => 'Подтверждение пароля',
        ],
        'group' => 'Контент',
    ],
    'media' => [
        'title' => 'Медиа',
        'single' => 'Медиа',
        'columns' => [
            'image' => 'Изображение',
            'model' => 'Модель',
            'collection_name' => 'Название коллекции',
            'size' => 'Размер',
            'order_column' => 'Очередность колонок',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => "Создать подпапку"
            ],
            'create' => [
                'label' => 'Добавить медиа',
                'form' => [
                    'file' => 'Файл',
                    'title' => 'Заголовок',
                    'description' => 'Описание',
                ],
            ],
            'delete' => [
                'label' => 'Удалить папку',
            ],
            'edit' => [
                'label' => 'Редактировать папку',
            ],
        ],
        'notifications' => [
            'create-media' => 'Медиа создано успешно',
            'delete-folder' => 'Папака удалена успешно',
            'edit-folder' => 'Папка отредактирована успешно',
        ],
        'meta' => [
            'model' => 'Модель',
            'file-name' => 'Название файла',
            'type' => 'Тип',
            'size' => 'Размер',
            'disk' => 'Диск',
            'url' => 'URL',
            'delete-media' => 'Удалить медиа',
        ],
    ],
];
