# 6. الجداول (حسب آخر تعديل)

الرسمة القديمة في [tax-erd.pdf](tax-erd.pdf) فيها `supervisors` و`inspectors` منفصلين. توا تغيّرت (شوف تحت).
كل الجداول بدون timestamps، ما عدا `employees`.

## القرار الأساسي
1. **جدول `users` واحد لتسجيل الدخول.** `role` تفرق: `admin` أو `supervisor` أو `inspector`.
2. **جدول `employees` واحد للمشرفين والمفتشين** بدل جدولين. فيه علاقة ذاتية:
   كل موظف عنده `supervisor_no` يأشّر على موظف ثاني في نفس الجدول.
3. الأدمن عنده صف في `users` بس، وما عندهش صف في `employees`.

```
employees
employee_no | supervisor_no | user_id
   E1       |  NULL         |  1      ← مشرف (users.role = supervisor)
   E2       |  E1           |  2      ← مفتش تحت E1
   E3       |  E1           |  3      ← مفتش تحت E1
```

## الجداول
| الجدول | المفتاح | الأعمدة | الروابط |
|---|---|---|---|
| users | `id` | name, email (unique), role (admin/supervisor/inspector), password | |
| regions | `region_id` | name | |
| employees | `employee_no` (نص) | title, photo_url?, timestamps | user_id → users (unique)، region_id → regions، supervisor_no? → employees (نفس الجدول، set null) |
| businesses | `tax_number` (نص) | name, activity_type, status | region_id → regions |
| tax_declarations | `declaration_id` | declaration_date, file_url | tax_number → businesses |
| visits | `visit_id` | visit_date, status, risk_level? | tax_number → businesses، employee_no → employees |
| reports | `report_id` | location (geography), report_time, qr_code (unique), status | visit_id → visits (unique) |
| report_photos | `photo_id` | photo_url | report_id → reports (cascade) |
| report_documents | `document_id` | document_url | report_id → reports (cascade) |
| violation_types | `violation_type_id` | name (unique) | |
| report_violations | (report_id, violation_type_id) | | جدول وسيط بدون موديل |

`?` = nullable.

## ترتيب الملفات
```
users (2014) → regions (2026_11_12) → employees (2026_11_22) → businesses → tax_declarations
→ visits → reports → report_photos → report_documents → violation_types → report_violations
```

## أسماء الأعمدة: كلها حروف صغيرة
PostgreSQL يفرّق بين `employee_No` و`employee_no` (لأن Laravel يحط الأسماء بين علامات تنصيص).
عشان هذا كل الأعمدة بحروف صغيرة و `_`، مثلاً `employee_no` و`supervisor_no`.

## اللي الداتا بيس ما تقدرش تتأكد منه
- `visits.employee_no` يقبل أي موظف، والداتا بيس ما تتأكدش أنه **مفتش**.
- `employees.supervisor_no` يقبل أي موظف، وما تتأكدش أنه **مشرف**.

السبب أن `role` في `users` مش في `employees`. التحقق منه يصير في الكود (validation)،
أو في الداتا بيس بـ composite FK (شوف [07-foreign-keys-postgis.md](07-foreign-keys-postgis.md)).

## الاسم: tax_declarations
كان `tax_returns`، وغيّرناه لـ `tax_declarations` (الإقرار الضريبي). الموديل `TaxDeclaration`.

## ليش المفاتيح بأسماء (region_id...) مش `id`؟
عشان تطابق الـ ERD، ويكون واضح في الـ FK شن يقصد. الثمن: لازم تكتب اسم المفتاح في
الموديل (`$primaryKey`) وفي العلاقات.
