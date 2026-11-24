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
