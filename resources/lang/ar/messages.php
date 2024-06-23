<?php

return [
    'folders' => [
        'title' => 'مدير الوسائط',
        'single' => 'مجلد',
        'columns' => [
            'name' => 'الاسم',
            'collection' => 'المجموعة',
            'description' => 'الوصف',
            'icon' => 'الايقونة',
            'color' => 'اللون',
            'is_protected' => 'محمي بكلمة مرور',
            'password' => 'كلمة المرور',
            'password_confirmation' => 'تأكيد كلمة المرور',
        ],
        'group' => 'المحتوي',
    ],
    'media' => [
        'title' => 'الوسائط',
        'single' => 'وسائط',
        'columns' => [
            'image' => 'الصورة',
            'model' => 'النموذج',
            'collection_name' => 'اسم المجموعة',
        ],
        'actions' => [
            'create' => [
                'label' => 'إضافة وسائط',
                'form' => [
                    'file' => 'الملف',
                    'title' => 'العنوان',
                    'description' => 'الوصف',
                ],
            ],
            'delete' => [
                'label' => 'حذف المجلد',
            ],
            'edit' => [
                'label' => 'تعديل المجلد',
            ],
        ],
        'notificaitons' => [
            'create-media' => 'تم إنشاء الوسائط بنجاح',
            'delete-folder' => 'تم حذف المجلد بنجاح',
            'edit-folder' => 'تم تعديل المجلد بنجاح',
        ],
    ],
];
