# 10. الحاجات الباقية

- [x] جدول `users`: `name` و`role` و`password` ولّوا مطلوبين.
- [x] دمج `supervisors` و`inspectors` في `employees` مع علاقة ذاتية (`supervisor_no`).
- [x] حذف `schema_tables.php` الفاضي.
- [x] توحيد أسماء الأعمدة بحروف صغيرة (`employee_no`) في الملفات.
- [ ] الداتا بيس فيها العمود القديم `employee_No`: لازم `migrate:fresh` (يمسح البيانات) أو migration فيها `renameColumn`.
- [ ] التحقق أن `visits.employee_no` مفتش و`supervisor_no` مشرف: في الكود (validation)، أو composite FK (شوف [07-foreign-keys-postgis.md](07-foreign-keys-postgis.md)).
- [ ] إرجاع migration جدول `personal_access_tokens` (Sanctum) قبل ما تبدا في تسجيل الدخول.
- [ ] تعبئة الـ Models: `Employee` فاضي، والباقين يحتاجوا `$primaryKey` و`$timestamps = false` و`$fillable` والعلاقات (شوف [04-models.md](04-models.md) و[08-relations.md](08-relations.md)).
- [ ] Seeder لأول مشرف، والبيانات من `.env`.
- [ ] تفعيل `extension=intl` في `C:\php\php.ini`.
- [ ] (اختياري) indexes لأعمدة الـ FK، وGIST index للـ location.
