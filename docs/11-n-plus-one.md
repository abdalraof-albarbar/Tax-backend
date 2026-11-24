# 11. مشكلة N+1

## شن هي
تجيب **قائمة** (N صف) باستعلام واحد، وبعدين لكل صف تطلب علاقته، فتعمل **استعلام زيادة لكل صف**.
المجموع: 1 + N استعلام بدل 2.

## مثال من مشروعنا: قائمة الزيارات مع اسم الشركة
```php
$visits = Visit::all();                 // استعلام 1: كل الزيارات (مثلاً 100)

foreach ($visits as $visit) {
    echo $visit->business->name;        // استعلام لكل زيارة ← 100 استعلام
}
```
الـ SQL اللي يتنفذ فعلاً:
```sql
SELECT * FROM visits;
SELECT * FROM businesses WHERE tax_number = 'A1';
SELECT * FROM businesses WHERE tax_number = 'B7';
SELECT * FROM businesses WHERE tax_number = 'A1';   -- نفس الشركة تنجاب مرة ثانية
... (100 مرة)
```
**101 استعلام.** ولو زدت `$visit->inspector->user->name` تولي 301.

## الحل: Eager Loading بـ `with()`
```php
$visits = Visit::with('business')->get();

foreach ($visits as $visit) {
    echo $visit->business->name;        // ما فيش استعلام هنا، الشركة جاهزة في الذاكرة
}
```
الـ SQL:
```sql
SELECT * FROM visits;
SELECT * FROM businesses WHERE tax_number IN ('A1', 'B7', ...);
```
**استعلامين بس**، سواء كانت 100 زيارة أو 10,000.

## بالرسم: المشاوير بين التطبيق والداتا بيس
كل سهم = استعلام واحد (مشوار).

**بدون `with()` (N = 5):**
```
 Laravel                                   PostgreSQL
┌─────────┐  1. SELECT * FROM visits      ┌──────────┐
│         │ ────────────────────────────► │          │
│ foreach │  2. ... WHERE tax_number='A1' │          │
│ visits  │ ────────────────────────────► │          │
│         │  3. ... WHERE tax_number='B7' │          │
│         │ ────────────────────────────► │          │
│         │  4. ... WHERE tax_number='A1' │ (متكرر!) │
│         │ ────────────────────────────► │          │
│         │  5. ... WHERE tax_number='C3' │          │
│         │ ────────────────────────────► │          │
│         │  6. ... WHERE tax_number='D2' │          │
│         │ ────────────────────────────► │          │
└─────────┘                               └──────────┘
                    = 6 مشاوير، ويكبروا مع N
```

**مع `with('business')`:**
```
 Laravel                                   PostgreSQL
┌─────────┐  1. SELECT * FROM visits      ┌──────────┐
│         │ ════════════════════════════► │          │
│ foreach │  2. ... WHERE tax_number      │          │
│ visits  │     IN ('A1','B7','C3','D2')  │          │
│         │ ════════════════════════════► │          │
└─────────┘                               └──────────┘
              = 2 مشاوير ديما، مهما كان N
```

| N (عدد الزيارات) | بدون `with()` | مع `with()` | الوقت التقريبي بدون / مع (2ms للاستعلام) |
|---|---|---|---|
| 5 | 6 | 2 | 12ms / 4ms |
| 100 | 101 | 2 | 202ms / 4ms |
| 10,000 | 10,001 | 2 | 20 ثانية / 4ms |

## تشبيه: النادل في المطعم
عندك **طاولة فيها 20 زبون**، وكل واحد طالب مشروب:

| الطريقة | شن يدير النادل | عدد المشاوير |
|---|---|---|
| **N+1** (`Visit::all()`) | ياخذ القائمة، وبعدين يمشي للمطبخ ويرجع **لكل زبون لوحده** | 1 + 20 = **21** |
| **Eager** (`with()`) | ياخذ القائمة، ويمشي للمطبخ **مرة وحدة** ويجيب الكل في صينية | 1 + 1 = **2** |

المطبخ هو الداتا بيس، والمشوار هو الاستعلام. التعب الحقيقي في **عدد المشاوير** (الشبكة، والاتصال، وتجهيز الاستعلام)، مش في كمية البيانات.

## ليش تكبر بسرعة: العلاقات المتداخلة
```php
foreach ($visits as $visit) {
    $visit->business->name;          // N استعلام
    $visit->inspector->user->name;   // N + N استعلام
    $visit->report->photos;          // N + N استعلام
}
```
مع 100 زيارة: بدون `with()` = 1 + 500 = **501 استعلام**.
مع `with('business', 'inspector.user', 'report.photos')` = 1 + 5 = **6 استعلامات**.

## القاعدة
> لو بتعمل **loop** على قائمة، وداخلها تكتب `->علاقة`، يبقى لازم `with('علاقة')` في الاستعلام الأول.

## عدة علاقات ومتداخلة
```php
Visit::with([
    'business',
    'inspector.user',          // المفتش، ومعاه حسابه في users
    'report.photos',           // التقرير، ومعاه صوره
    'report.violationTypes',
])->get();
```
كل علاقة = استعلام واحد زيادة (مش واحد لكل صف).

## ليش خطيرة
- وانت تجرّب بـ 5 صفوف ما تحسّش بيها.
- لما البيانات تكبر، الـ API يبطى بزاف، وتطبيق Flutter يستنى.
- في JSON API تصير بزاف، لأن `return $visits` مع علاقات في الـ Resource يناديها لكل صف.

## كيف تكتشفها
1. **امنعها في وقت التطوير.** زيد هذا في `AppServiceProvider::boot()`:
   ```php
   Model::preventLazyLoading(! app()->isProduction());
   ```
   أي علاقة تنجاب بدون `with()` تطلع exception، فتعرف فوراً وين المشكلة.
2. **عدّ الاستعلامات:**
   ```php
   DB::enableQueryLog();
   // ... الكود
   dd(count(DB::getQueryLog()));
   ```
3. **Laravel Debugbar أو Telescope** يورّوك عدد الاستعلامات لكل request.

## حالات خاصة
| الحالة | الحل |
|---|---|
| جبت القائمة، ومن بعد قررت تحتاج العلاقة | `$visits->load('business');` |
| تبي **عدد** بس (مثلاً عدد زيارات كل مفتش) | `Inspector::withCount('visits')->get();` ← `visits_count` |
| تبي أعمدة معيّنة من العلاقة | `with('business:tax_number,name')` (لازم تحط المفتاح) |
| علاقة واحدة على صف واحد (`find`) | عادي، ما فيش N+1، لأن N = 1 |

## ربطها بالـ index
`with('photos')` يعمل `WHERE report_id IN (...)`. لو `report_id` بدون index، الاستعلام هذا يبطى مع
البيانات الكبيرة. شوف `problems/007-fk-columns-without-index.md`.
