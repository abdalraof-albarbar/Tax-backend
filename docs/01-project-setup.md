# 1. تجهيز البيئة: PHP 8.5 وcomposer وDocker

## المشكلة اللي صارت
```
Root composer.json requires php ^8.5 ... your php version (8.4.0) does not satisfy that requirement
```
معناها: المشروع يطلب PHP 8.5، والـ PHP اللي في الـ PATH كانت 8.4.

## الحل
- نزّلنا PHP 8.5 وحطيناه في `C:\php`.
- حطينا `C:\php` في **أول** الـ PATH، عشان الويندوز يلقاه قبل أي نسخة قديمة.
- تتأكد بالأمر:
```powershell
php -v
```

## خطأ platform_check
لما يطلع fatal error من `vendor/composer/platform_check.php`، معناها أن الحزم في
`vendor` تنزّلت على نسخة PHP تختلف عن اللي تشغّل بيها توا. الحل أنك تشغّل
بنفس النسخة، أو تعاود `composer install`.

## composer fund
الرسالة اللي تجي بعد `composer install` وتقول أن فيه حزم تبي تبرعات. هذا إعلان بس، وما يأثر على شي.

## تحذير LF / CRLF
```
warning: LF will be replaced by CRLF
```
الويندوز ينهي السطر بـ `CRLF`، ولينكس ينهيه بـ `LF`. git يقولك أنه حيحوّل نهاية السطر،
وهذا تحذير مش خطأ.

## Docker
- صورة PHP: `php:8.5-cli-bookworm` (bookworm هي Debian 12). مناسبة للمشروع.
- الداتا بيس PostgreSQL + PostGIS على السيرفر في كونتينر `tax_database`.
- الـ `.env` متصل بالسيرفر عن طريق نفق (tunnel) على البورت `55000`.
  **انتبه:** أي `migrate:fresh` يمسح جداول السيرفر.

## إضافة intl (ناقصة)
`php artisan db:table` يطلب إضافة `intl`. تفعّلها من `C:\php\php.ini` بحذف `;` من قدام السطر:
```ini
extension=intl
```
