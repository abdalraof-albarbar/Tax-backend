# 002: جدول التوكنز ناقص (personal_access_tokens)

- **الحالة:** ⏳ مفتوحة
- **الخطورة:** عالية (لما تبدا تسجيل الدخول)
- **الملف:** `database/migrations/` (الملف محذوف)

## شن المشكلة
`User` فيه `HasApiTokens` (Sanctum)، لكن جدول `personal_access_tokens` مش موجود.
حذفناه بعد خطأ `SQLSTATE[42701]`، لأن عمود `name` كان مكرر فيه.

## ليش تأثر
أول ما تنادي `$user->createToken(...)` في الـ login يطلع خطأ: الجدول مش موجود.
فـ Flutter وReact ما يقدروش يسجلوا دخول.

## كيف تتصلح
```powershell
php artisan vendor:publish --tag=sanctum-migrations
```
بعدها تأكد أن `name` مكتوب **مرة وحدة** في الملف، وشغّل `php artisan migrate`.

## الحل
