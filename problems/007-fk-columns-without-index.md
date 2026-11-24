# 007: أعمدة الـ FK بدون index

- **الحالة:** ⏳ مفتوحة (تحسين، مش خطأ)
- **الخطورة:** منخفضة توا، وتكبر مع البيانات
- **الملفات:** migrations امتاع `inspectors` و`businesses` و`tax_declarations` و`visits` و`report_photos` و`report_documents`

## شن المشكلة
PostgreSQL يعمل index تلقائي للـ `primary` والـ `unique` بس، مش لأعمدة الـ FK.
يعني `visits.inspector_no` و`visits.tax_number` و`inspectors.region_id` وغيرهم بدون index.
وعمود `reports.location` بدون GIST index.

## ليش تأثر
البحث مثل "زيارات المفتش X" أو "صور التقرير Y" يخلي الداتا بيس تقرا الجدول كامل.
مع آلاف الصفوف يصير بطيء، وخاصة مع `with()` (شوف `docs/11-n-plus-one.md`).

## كيف تتصلح
في migration جديدة:
```php
$table->index('inspector_no');
```
وللموقع:
```sql
CREATE INDEX reports_location_gist ON reports USING GIST (location);
```

## الحل
