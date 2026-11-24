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
