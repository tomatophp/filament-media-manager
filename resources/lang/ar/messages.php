<?php

return [
    'empty' => [
        'title' => "لا يوجد وسائط أو مجلدات",
    ],
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
            'size' => 'الججم',
            'order_column' => 'عمود الترتيب',
        ],
        'actions' => [
            'sub_folder'=> [
                'label' => "إنشاء مجلد فرعي"
            ],
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
        'notifications' => [
            'create-media' => 'تم إنشاء الوسائط بنجاح',
            'delete-folder' => 'تم حذف المجلد بنجاح',
            'edit-folder' => 'تم تعديل المجلد بنجاح',
        ],
        'meta' => [
            'model' => 'نموذج',
            'file-name' => 'اسم الملف',
            'type' => 'نوع',
            'size' => 'حجم',
            'disk' => 'قرص',
            'url' => 'رابط',
            'delete-media' => 'حذف الوسائط',
        ],
    ],
];
