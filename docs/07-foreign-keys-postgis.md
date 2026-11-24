# 7. المفاتيح الأجنبية (FK) والـ geography

## `foreignId` (المفتاح رقم)
```php
$table->foreignId('visit_id')->unique()->constrained('visits', 'visit_id');
```
| الجزء | المعنى |
|---|---|
| `foreignId('visit_id')` | عمود رقم كبير (bigint) اسمه `visit_id` |
| `->unique()` | الزيارة ليها تقرير **واحد** بس (علاقة 1 لـ 1) |
| `->constrained('visits', 'visit_id')` | لازم القيمة تكون موجودة في `visits.visit_id` |
| `->cascadeOnDelete()` | لو انحذف الأب، ينحذفوا الأبناء معاه |

## `foreign` (المفتاح نص)
`foreignId` للأرقام بس. لما المفتاح نص (`tax_number`، `employee_no`):
```php
$table->string('inspector_no');
$table->foreign('inspector_no')->references('employee_no')->on('inspectors');
```

## FK ما يتعملش لو الأب مش موجود
عشان هذا ترتيب الملفات مهم: `regions` ← `supervisors` ← `inspectors` ← `businesses` ← ...

## `geography` (PostGIS)
```php
$table->geography('location', subtype: 'point', srid: 4326);
```
| الجزء | المعنى |
|---|---|
| `geography` | نوع عمود من PostGIS يخزّن مكان على الكرة الأرضية |
| `subtype: 'point'` | نقطة وحدة (مش خط ولا مساحة) |
| `srid: 4326` | نظام إحداثيات GPS (خط الطول والعرض)، نفس اللي يطلع من الموبايل |

### حفظ نقطة
**الترتيب: الطول (lng) أولاً، بعدين العرض (lat).**
```php
DB::statement(
    "INSERT INTO reports (location, ...) VALUES (ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ...)",
    [$lng, $lat, ...]
);
```

### بحث بالمسافة (التقارير اللي في 500 متر)
```sql
SELECT * FROM reports
WHERE ST_DWithin(location, ST_MakePoint(13.18, 32.88)::geography, 500);
```
`geography` يحسب المسافة بالمتر.

### تسريع البحث
```sql
CREATE INDEX reports_location_gist ON reports USING GIST (location);
```
