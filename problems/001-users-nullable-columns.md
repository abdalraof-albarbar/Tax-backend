# 001: أعمدة users اختيارية (nullable)

- **الحالة:** ⏳ مفتوحة
- **الخطورة:** عالية
- **الملف:** `database/migrations/2014_10_12_000000_create_users_table.php`

## شن المشكلة
```php
$table->string('name')->nullable();
$table->enum('role', ['supervisor', 'inspector'])->nullable();
$table->string('password')->nullable();
```

## ليش تأثر
- ممكن يتسجل مستخدم **بدون كلمة سر** أو **بدون role**.
- مستخدم بدون role ما نعرفوش مشرف ولا مفتش، فالصلاحيات تتلخبط.
- `role` مش في `$fillable` (وهذا صح)، فلو نسيت تعطيها قيمة في الكود تنحفظ `null` بدون أي خطأ.

## كيف تتصلح
الجدول اتنفّذ على السيرفر، فما تعدّلش الملف القديم. اعمل migration جديدة فيها
`->nullable(false)->change()` للأعمدة الثلاثة (شوف `docs/05-migrations.md`).
قبلها تأكد ما فيش صفوف فيها `null`، وإلا الـ migration تفشل.

## الحل
