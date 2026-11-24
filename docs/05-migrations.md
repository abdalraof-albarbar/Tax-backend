# 5. الـ Migrations

الـ migration ملف PHP يوصف الجدول. Laravel يحوّله لـ SQL وينفّذه على الداتا بيس.

## ترتيب التنفيذ
حسب الوقت في أول اسم الملف (`2026_11_23_204537_...`). الجدول الأب لازم يتنفذ قبل الابن
اللي فيه FK. عشان هذا خذا `businesses` الوقت `204700`، باش يتنفذ قبل `tax_declarations`.

## إنشاء ملف
```powershell
php artisan make:model Region -m        # موديل + migration
php artisan make:migration create_regions_table
```
أو تنسخ ملف موجود وتغيّر الاسم والوقت. الأوامر مش إجبارية.

## الأعمدة: مطلوب أو اختياري
- افتراضياً كل عمود **مطلوب** (NOT NULL).
- `->nullable()` تخليه اختياري.
- `->unique()` تمنع التكرار.
- `->default('قيمة')` قيمة افتراضية.

## الأوامر
| الأمر | شن يدير |
|---|---|
| `php artisan migrate` | ينفّذ الملفات الجديدة بس |
| `php artisan migrate:status` | يوريك شن اتنفّذ (Ran) وشن لا |
| `php artisan migrate --pretend` | يوريك الـ SQL بدون ما ينفّذ |
| `php artisan migrate:rollback` | يرجّع آخر دفعة (batch) بتشغيل `down()` |
| `php artisan migrate:fresh` | ⚠️ يمسح **كل** الجداول ويعاود ينشئهم (البيانات تضيع) |

### refresh قدام fresh
```
migrate:refresh = down() لكل ملف بالعكس، بعدين up() لكلهم   ← الأبطأ، ويحتاج down() صحيح
migrate:fresh   = DROP لكل الجداول مرة وحدة، بعدين up()      ← أسرع
```
الاثنين **يمسحوا كل البيانات**. عندنا بطيئين زيادة لأن `.env` على داتا بيس السيرفر عن طريق نفق SSH،
وكل أمر SQL يمشي ويرجع في الشبكة. الحل: داتا بيس محلية (شوف `problems/004`).

Laravel يسجّل الملفات اللي اتنفّذت في جدول اسمه `migrations`. عشان هذا تعديل
ملف قديم ما يأثرش لين تعمل rollback أو fresh.

## تعديل جدول موجود (فيه بيانات)
ما تعدّلش الملف القديم. اعمل migration جديدة:
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name')->nullable(false)->change();   // يخليه مطلوب
    $table->dropColumn('remember_token');                // يحذف عمود
    $table->dropTimestamps();                            // يحذف created_at وupdated_at
});
```

## ملاحظات PostgreSQL
- كل migration تتنفّذ داخل transaction. لو صار خطأ في نصها، يرجع كل شي كأنه ما صار.
- `$table->id()->primary()`: الـ `primary()` زايدة، لأن `id()` أصلاً primary. لكنها ما تكسرش شي.

## أخطاء صارت عندنا
| الخطأ | السبب |
|---|---|
| `SQLSTATE[42701] duplicate column name` | عمود مكتوب مرتين في نفس الجدول |
| `down()` يحذف اسم غلط (`visit` بدل `visits`) | لازم `dropIfExists` بنفس اسم الجدول في `up()` |
| `column "user_id" referenced in foreign key constraint does not exist` | `constrained('users', 'user_id')`، والمفتاح في `users` اسمه `id`. الصح `constrained('users')` |
| `incompatible types: bigint and character varying` | `foreignId` (رقم) يأشّر على مفتاح نص. الصح `string` + `foreign()` |
| `SQLSTATE[42830] there is no unique constraint matching given keys` | FK ذاتي داخل `Schema::create` قبل ما الـ primary key النصي يتعمل. الصح تحطه في `Schema::table` منفصل |
| `relation "regions" already exists` | مسحت ملف migration قديم وعملت واحد جديد باسم مختلف، والجدول لسا في الداتا بيس |

## تعديل ملف migration بعد ما تنفّذ
`migrate:status` يقول `Ran`، يعني الجدول في الداتا بيس بالشكل القديم. تعديل الملف **وحده ما يغيّرش شي**.
- **في التطوير (ما فيش بيانات مهمة):** `php artisan migrate:fresh` ⚠️ يمسح الكل ويعاود يبني من الملفات.
- **لو فيه بيانات:** migration جديدة تعدّل، مثلاً `$table->renameColumn('employee_No', 'employee_no');`.
