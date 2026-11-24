# 6. الجداول (حسب الـ ERD)

الرسمة في [tax-erd.pdf](tax-erd.pdf). كل الجداول بدون timestamps.

## القرار الأساسي: جدول `users` واحد
كل اللي يسجلوا دخول (مشرف أو مفتش) في `users`، و`role` تفرق بيناتهم.
تفاصيل الوظيفة في `supervisors` أو `inspectors`، مربوطة بـ `user_id`.
`name` و`email` انتقلوا من جدولي المشرف والمفتش لـ `users`. ما فيش دور `admin`.
أول مشرف يتعمل بـ seeder.

## الجداول
| الجدول | المفتاح | الأعمدة | الروابط |
|---|---|---|---|
| users | `id` | name, email (unique), role (supervisor/inspector), password | |
| regions | `region_id` | name | |
| supervisors | `employee_no` (نص) | title?, photo_url? | user_id → users (unique) |
| inspectors | `employee_no` (نص) | title?, photo_url? | region_id → regions، user_id → users (unique)، supervisor_no → supervisors |
| businesses | `tax_number` (نص) | name, activity_type, status | region_id → regions |
| tax_declarations | `declaration_id` | declaration_date, file_url | tax_number → businesses |
| visits | `visit_id` | visit_date, status, risk_level? | tax_number → businesses، inspector_no → inspectors |
| reports | `report_id` | location (geography), report_time, qr_code (unique), status | visit_id → visits (unique) |
| report_photos | `photo_id` | photo_url | report_id → reports (cascade) |
| report_documents | `document_id` | document_url | report_id → reports (cascade) |
| violation_types | `violation_type_id` | name (unique) | |
| report_violations | (report_id, violation_type_id) | | جدول وسيط بدون موديل |

`?` = nullable.

## الاسم: tax_declarations
كان `tax_returns`، وغيّرناه لـ `tax_declarations` (الإقرار الضريبي). الموديل `TaxDeclaration`.

## ليش المفاتيح بأسماء (region_id...) مش `id`؟
عشان تطابق الـ ERD، ويكون واضح في الـ FK شن يقصد. الثمن: لازم تكتب اسم المفتاح في
الموديل (`$primaryKey`) وفي العلاقات.
