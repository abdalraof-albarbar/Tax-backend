# 003: الـ Models ناقصة إعدادات

- **الحالة:** ⏳ مفتوحة
- **الخطورة:** عالية
- **الملفات:** `app/Models/*.php` (كلهم ما عدا `User`)

## شن المشكلة
- `Region` و`Business` و`Visit` و`Report` وغيرهم فاضيين (`//`).
- `Inspector` ما فيهش `public $timestamps = false;`، و`user_id` مش في `$fillable`.

## ليش تأثر
| الناقص | شن يصير |
|---|---|
| `$timestamps = false` | أي `create()` يحاول يكتب `created_at` و`updated_at` ← خطأ: العمود مش موجود |
| `$primaryKey` (مثل `region_id`) | `find(1)` يدوّر على عمود `id` ← خطأ |
| `$incrementing = false` و`$keyType = 'string'` للمفاتيح النصية | Laravel يتعامل مع `tax_number` على أنه رقم، ويرجّع قيمة غلط بعد الحفظ |
| `$fillable` | `create([...])` يتجاهل الأعمدة، فتنحفظ فاضية أو يطلع خطأ NOT NULL |
| العلاقات | `$inspector->region` ما تخدمش |

## كيف تتصلح
لكل موديل: `$primaryKey` و`$timestamps = false` و`$fillable` والعلاقات.
شوف `docs/04-models.md` و`docs/08-relations.md`.

## الحل
