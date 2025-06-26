<?php

return [
    'empty' => [
        'title' => "Medya veya Klasör Bulunamadı",
    ],
    'folders' => [
        'title' => 'Medya Yöneticisi',
        'single' => 'Klasör',
        'columns' => [
            'name' => 'Ad',
            'collection' => 'Koleksiyon',
            'description' => 'Açıklama',
            'is_public' => 'Herkese Açık mı',
            'has_user_access' => 'Kullanıcı Erişimi Var mı',
            'users' => 'Kullanıcılar',
            'icon' => 'Simge',
            'color' => 'Renk',
            'is_protected' => 'Korumalı mı',
            'password' => 'Parola',
            'password_confirmation' => 'Parola Onayı',
        ],
        'group' => 'İçerik',
    ],
    'media' => [
        'title' => 'Medya',
        'single' => 'Medya',
        'columns' => [
            'image' => 'Görsel',
            'model' => 'Model',
            'collection_name' => 'Koleksiyon Adı',
            'size' => 'Boyut',
            'order_column' => 'Sıra Sütunu',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => "Alt Klasör Oluştur"
            ],
            'create' => [
                'label' => 'Medya Ekle',
                'form' => [
                    'file' => 'Dosya',
                    'title' => 'Başlık',
                    'description' => 'Açıklama',
                ],
            ],
            'delete' => [
                'label' => 'Klasörü Sil',
            ],
            'edit' => [
                'label' => 'Klasörü Düzenle',
            ],
        ],
        'notifications' => [
            'create-media' => 'Medya başarıyla oluşturuldu',
            'delete-folder' => 'Klasör başarıyla silindi',
            'edit-folder' => 'Klasör başarıyla düzenlendi',
        ],
        'meta' => [
            'model' => 'Model',
            'file-name' => 'Dosya Adı',
            'type' => 'Tür',
            'size' => 'Boyut',
            'disk' => 'Disk',
            'url' => 'URL',
            'delete-media' => 'Medya Sil',
        ],
    ],
];
