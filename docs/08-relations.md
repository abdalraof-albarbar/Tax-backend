# 8. العلاقات بين الـ Models

العلاقة دالة في الموديل، ترجّع الصفوف المربوطة بالـ FK.

## الأنواع
| النوع | وين تحطها | مثال |
|---|---|---|
| `belongsTo` | الجدول اللي **فيه** عمود الـ FK | Employee → Region |
| `hasMany` | الجدول الأب (1 لـ N) | Region → Employees |
| `hasOne` | الجدول الأب (1 لـ 1) | User → Employee |
| `belongsToMany` | عبر جدول وسيط | Report ↔ ViolationType |

## ليش أحياناً نكتب أسماء المفاتيح وأحياناً لا؟
Laravel يخمّن اسم الـ FK = **اسم العلاقة + `_` + المفتاح الأساسي للجدول الثاني**.
- `region()` + `region_id` = `region_id` ← يطابق، فما تحتاجش تكتبه.
- `supervisor()` + `employee_no` = `supervisor_employee_no` ← العمود عندنا `supervisor_no`، فلازم تكتبه.

```php
// Employee.php
protected $primaryKey = 'employee_no';
public $incrementing = false;
protected $keyType = 'string';

public function region(): BelongsTo
{
    return $this->belongsTo(Region::class, 'region_id', 'region_id');
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);   // user_id → users.id: الافتراضي يمشي
}

public function visits(): HasMany
{
    return $this->hasMany(Visit::class, 'employee_no', 'employee_no');
}
```

## العلاقة الذاتية (Self-referencing)
ما فيش نوع خاص في Eloquent. نفس `belongsTo` و`hasMany`، لكن **الموديل يأشّر على روحه**:
```php
// Employee.php
public function supervisor(): BelongsTo        // المشرف امتاعي (واحد، لفوق)
{
    return $this->belongsTo(Employee::class, 'supervisor_no', 'employee_no');
}

public function subordinates(): HasMany        // اللي تحتي (ياسر، لتحت)
{
    return $this->hasMany(Employee::class, 'supervisor_no', 'employee_no');
}
```
```
employee_no | supervisor_no
   E1       |  NULL     ← مشرف
   E2       |  E1
   E3       |  E1
```
```php
Employee::find('E2')->supervisor;      // E1
Employee::find('E1')->subordinates;    // [E2, E3]
Employee::find('E1')->supervisor;      // null
```
- الموديل واحد، وأسماء الدوال هي اللي توضح الاتجاه.
- مين مفتش ومين مشرف يتحدد من `users.role`، والجدول يحدد **مين تحت مين** بس.
- في الـ migration، الـ FK الذاتي لازم يكون في `Schema::table` منفصل (شوف `07-foreign-keys-postgis.md`).

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

### ليش الجدول الوسيط ما يحتاجش موديل
```
 reports            report_violations          violation_types
┌───────────┐      ┌─────────────────────┐     ┌──────────────────┐
│ report_id │◄────►│ report_id │ type_id │◄───►│ violation_type_id│
│ 10        │      │ 10        │ 1       │     │ 1  no license    │
│ 11        │      │ 10        │ 3       │     │ 3  no invoices   │
└───────────┘      │ 11        │ 1       │     └──────────────────┘
   Report          └─────────────────────┘       ViolationType
   (model)           ما عنده موديل                (model)
```
الموديل يلزمك لما الجدول يمثّل **شي حقيقي** عنده بيانات خاصة بيه. `report_violations` **رابط بس**:
الصف `10 | 3` معناه "التقرير 10 فيه المخالفة 3"، وLaravel يوصله عن طريق `belongsToMany`.

لازم تكتب الأسماء كاملة. لو ما كتبتهاش، Laravel يخمّن الجدول `report_violation_type`
(ترتيب أبجدي بالمفرد) والمفاتيح `id`، وكله غلط عندنا.

| الكود | شن يدير في `report_violations` |
|---|---|
| `attach([1, 3])` | يضيف صفوف |
| `detach(3)` | يمسح صف |
| `sync([1, 5])` | يخلي 1 و5 بس، ويمسح الباقي |
| `$report->violationTypes` | يجيب المخالفات |
| `$type->reports` | يجيب التقارير اللي فيها المخالفة هذي |

**متى تحتاج موديل للوسيط؟** لما تزيد فيه أعمدة خاصة بيه (مثل `fine_amount`):
- عمود أو اثنين: `->withPivot('fine_amount')`، وتقراه بـ `$type->pivot->fine_amount`.
- منطق خاص بيه: موديل يورث من `Pivot`، وتربطه بـ `->using(ReportViolation::class)`.

## الاستعمال
```php
$employee->region->name;          // اسم منطقة الموظف
$employee->visits;                // كل زياراته
Employee::with('region')->get();  // يجيب المناطق مع الموظفين في استعلام واحد
```

## الترتيب
أنشئ الجداول (migrations) أولاً، بعدين اكتب العلاقات. العلاقات كود PHP بس، وما تغيّرش الداتا بيس.

## خريطة العلاقات في مشروعنا
| من | إلى | النوع | الدالة في الأب | الدالة في الابن |
|---|---|---|---|---|
| User | Employee | 1 : 0..1 | `employee()` hasOne | `user()` belongsTo |
| Employee (مشرف) | Employee | 1 : N (ذاتية) | `subordinates()` hasMany | `supervisor()` belongsTo |
| Region | Employee | 1 : N | `employees()` hasMany | `region()` belongsTo |
| Region | Business | 1 : N | `businesses()` hasMany | `region()` belongsTo |
| Business | TaxDeclaration | 1 : N | `taxDeclarations()` hasMany | `business()` belongsTo |
| Business | Visit | 1 : N | `visits()` hasMany | `business()` belongsTo |
| Employee (مفتش) | Visit | 1 : N | `visits()` hasMany | `employee()` belongsTo |
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
   Visit::with(['business', 'employee.user', 'report.photos'])->get();
   ```
   بدون `with`، كل زيارة تعمل استعلام لوحدها (100 زيارة = 101 استعلام).
5. **cascade بحذر:** خليناه بس للصور والمستندات والمخالفات (توابع التقرير). ما تحطهوش على `businesses` أو `employees`، باش ما تنحذفش بيانات مهمة بالغلط.
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
   Employee::first()->supervisor
   ```
