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

### `foreignId` للأرقام بس
`foreignId('x')` = `unsignedBigInteger('x')`، فيتطابق بس مع مفتاح `id()` (bigint).
**نوع العمود في الابن لازم نفس نوع المفتاح في الأب.**

| الأب | الابن |
|---|---|
| `id()` / `id('region_id')` | `foreignId(...)->constrained(...)` |
| `string(...)->primary()` | `string(...)` + `foreign(...)->references(...)->on(...)` |
| `uuid` / `ulid` | `foreignUuid` / `foreignUlid` |

لو درت `foreignId` لعمود يأشّر على مفتاح نص، يطلع خطأ
`incompatible types: bigint and character varying`.

## FK ما يتعملش لو الأب مش موجود
عشان هذا ترتيب الملفات مهم: `users` ← `regions` ← `employees` ← `businesses` ← ...

## العلاقة الذاتية (الجدول يأشّر على روحه)
`employees.supervisor_no` يأشّر على `employees.employee_no`.

**المشكلة:** لما المفتاح نص (`string(...)->primary()`)، Laravel يضيف الـ primary key كآخر أمر في `Schema::create`،
**بعد** الـ FK. فلما يتعمل FK المشرف، `employee_no` لسا مش primary، والداتا بيس ترفض:
```
SQLSTATE[42830]: there is no unique constraint matching given keys for referenced table "employees"
```
القاعدة: **العمود اللي يأشّر عليه الـ FK لازم يكون primary أو unique في لحظة إنشاء الـ FK.**

**الحل:** الـ FK الذاتي في `Schema::table` منفصل، بعد ما `Schema::create` يخلص:
```php
Schema::create('employees', function (Blueprint $table) {
    $table->string('employee_no')->primary();
    $table->string('supervisor_no')->nullable();   // nullable: أول مشرف ما عندهش مشرف
    // ...
});

Schema::table('employees', function (Blueprint $table) {
    $table->foreign('supervisor_no')->references('employee_no')->on('employees')
          ->onDelete('set null');                  // لو انحذف المشرف، يولّي null
});
```
تتأكد من الترتيب بـ `php artisan migrate --pretend`.

## التأكد من الدور في الداتا بيس (composite FK)
الـ FK العادي يتأكد أن **الموظف موجود**، مش أنه **مفتش**. لو تبي الداتا بيس نفسها تمنع زيارة لمشرف:
1. تحط `role` في `employees`، وتعمل `unique(['employee_no', 'role'])`.
2. في `visits` تزيد عمود ثابت `employee_role` قيمته ديما `'inspector'`.
3. FK على الزوج:
```php
$table->foreign(['employee_no', 'employee_role'])
      ->references(['employee_no', 'role'])->on('employees');
```
هكي لو `E1` مشرف، الزوج `(E1, inspector)` مش موجود، فالداتا بيس ترفض.
العيب: `role` يتكرر في `users` و`employees`، فلازم الكود يخليهم متطابقين.
البديل الأبسط: التحقق في الكود (validation) قبل الحفظ.

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
