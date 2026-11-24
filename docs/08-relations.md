# 8. العلاقات بين الـ Models

العلاقة دالة في الموديل، ترجّع الصفوف المربوطة بالـ FK.

## الأنواع
| النوع | وين تحطها | مثال |
|---|---|---|
| `belongsTo` | الجدول اللي **فيه** عمود الـ FK | Inspector → Region |
| `hasMany` | الجدول الأب (1 لـ N) | Region → Inspectors |
| `hasOne` | الجدول الأب (1 لـ 1) | User → Inspector |
| `belongsToMany` | عبر جدول وسيط | Report ↔ ViolationType |

## ليش أحياناً نكتب أسماء المفاتيح وأحياناً لا؟
Laravel يخمّن اسم الـ FK = **اسم العلاقة + `_` + المفتاح الأساسي للجدول الثاني**.
- `region()` + `region_id` = `region_id` ← يطابق، فما تحتاجش تكتبه.
- `supervisor()` + `employee_no` = `supervisor_employee_no` ← العمود عندنا `supervisor_no`، فلازم تكتبه.

```php
// Inspector.php
public function region(): BelongsTo
{
    return $this->belongsTo(Region::class, 'region_id', 'region_id');
}

public function supervisor(): BelongsTo
{
    return $this->belongsTo(Supervisor::class, 'supervisor_no', 'employee_no');
    //                                         FK في inspectors  المفتاح في supervisors
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);   // user_id → users.id: الافتراضي يمشي
}

public function visits(): HasMany
{
    return $this->hasMany(Visit::class, 'inspector_no', 'employee_no');
}
```

## الجدول الوسيط (report_violations)
ما عنده موديل. نستعمله من الطرفين:
```php
// Report.php
public function violationTypes(): BelongsToMany
{
    return $this->belongsToMany(
        ViolationType::class, 'report_violations', 'report_id', 'violation_type_id'
    );
}
```
```php
$report->violationTypes()->attach([1, 3]);   // يضيف مخالفتين للتقرير
```

## الاستعمال
```php
$inspector->region->name;        // اسم منطقة المفتش
$inspector->visits;              // كل زياراته
Inspector::with('region')->get(); // يجيب المناطق مع المفتشين في استعلام واحد
```

## الترتيب
أنشئ الجداول (migrations) أولاً، بعدين اكتب العلاقات. العلاقات كود PHP بس، وما تغيّرش الداتا بيس.

## خريطة العلاقات في مشروعنا
| من | إلى | النوع | الدالة في الأب | الدالة في الابن |
|---|---|---|---|---|
| User | Supervisor | 1 : 0..1 | `supervisor()` hasOne | `user()` belongsTo |
| User | Inspector | 1 : 0..1 | `inspector()` hasOne | `user()` belongsTo |
| Supervisor | Inspector | 1 : N | `inspectors()` hasMany | `supervisor()` belongsTo |
| Region | Inspector | 1 : N | `inspectors()` hasMany | `region()` belongsTo |
| Region | Business | 1 : N | `businesses()` hasMany | `region()` belongsTo |
| Business | TaxDeclaration | 1 : N | `taxDeclarations()` hasMany | `business()` belongsTo |
| Business | Visit | 1 : N | `visits()` hasMany | `business()` belongsTo |
| Inspector | Visit | 1 : N | `visits()` hasMany | `inspector()` belongsTo |
| Visit | Report | 1 : 0..1 | `report()` hasOne | `visit()` belongsTo |
| Report | ReportPhoto | 1 : N | `photos()` hasMany | `report()` belongsTo |
| Report | ReportDocument | 1 : N | `documents()` hasMany | `report()` belongsTo |
| Report | ViolationType | N : M | `violationTypes()` belongsToMany | `reports()` belongsToMany |

**قاعدة سهلة:** الجدول اللي فيه عمود الـ FK ياخذ `belongsTo`، والجدول الثاني ياخذ `hasOne` أو `hasMany`.
لو الـ FK عليه `unique` تكون `hasOne`، ولو بدونه تكون `hasMany`.

## نصائح
1. **اكتب العلاقة من الطرفين** (`hasMany` في الأب و`belongsTo` في الابن)، عشان تقدر تمشي في الاتجاهين.
2. **اسم الدالة:** مفرد لـ `belongsTo`/`hasOne` (`region()`)، وجمع لـ `hasMany`/`belongsToMany` (`visits()`).
3. **أسماء المفاتيح المخصصة:** كل ما يكون المفتاح مش `id`، اكتب المفتاحين صراحة. عندنا تقريباً كل العلاقات تحتاج هذا.
4. **تجنب مشكلة N+1:** لما تجيب قائمة وتحتاج علاقاتها، استعمل `with()`:
   ```php
   Visit::with(['business', 'inspector.user', 'report.photos'])->get();
   ```
   بدون `with`، كل زيارة تعمل استعلام لوحدها (100 زيارة = 101 استعلام).
5. **cascade بحذر:** خليناه بس للصور والمستندات والمخالفات (توابع التقرير). ما تحطهوش على `businesses` أو `inspectors`، باش ما تنحذفش بيانات مهمة بالغلط.
6. **أضف index لأعمدة الـ FK.** PostgreSQL ما يعملهمش تلقائياً، والبحث بيها (مثل زيارات مفتش) يصير أسرع.
7. **الجدول الوسيط** ما يحتاجش موديل. استعمل `attach` و`detach` و`sync`:
   ```php
   $report->violationTypes()->sync([1, 3]);   // يخلي المخالفات 1 و3 بس
   ```
8. **جرّب العلاقات في tinker** قبل ما تكتب الكنترولر:
   ```powershell
   php artisan tinker
   ```
   ```php
   Inspector::first()->region
   ```
