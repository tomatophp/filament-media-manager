<?php

return [
    'empty' => [
        'title' => 'لا يوجد وسائط أو مجلدات',
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
        'filters' => [
            'all_folders' => 'كل المجلدات',
            'protected_only' => 'المحمية فقط',
            'public_only' => 'العامة فقط',
            'created_from' => 'تم الإنشاء من',
            'created_until' => 'تم الإنشاء حتى',
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
        'filters' => [
            'size_from' => 'الحجم من (كيلوبايت)',
            'size_to' => 'الحجم إلى (كيلوبايت)',
            'created_from' => 'تم الإنشاء من',
            'created_until' => 'تم الإنشاء حتى',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => 'إنشاء مجلد فرعي',
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
    'picker' => [
        'title' => 'اختر الوسائط',
        'browse' => 'تصفح الوسائط',
        'remove' => 'إزالة',
        'select' => 'اختيار',
        'cancel' => 'إلغاء',
        'back' => 'رجوع',
        'search' => 'بحث في المجلدات والملفات...',
        'select_folder' => 'اختر مجلد لتصفح ملفات الوسائط',
        'folders' => 'المجلدات',
        'media_files' => 'ملفات الوسائط',
        'empty' => 'لا توجد مجلدات أو ملفات وسائط',
        'no_media_selected' => 'لم يتم اختيار وسائط',
        'selected' => 'محدد',
        'clear_all' => 'مسح الكل',
        'confirm_remove' => 'إزالة الوسائط',
        'confirm_remove_message' => 'هل أنت متأكد من إزالة هذا العنصر؟',
    ],
];
