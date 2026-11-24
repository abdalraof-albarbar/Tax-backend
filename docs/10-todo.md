# 10. الحاجات الباقية

- [ ] جدول `users`: `name` و`role` و`password` حالياً `nullable`. ردّهم مطلوبين بـ migration جديدة فيها `->change()` (شوف [05-migrations.md](05-migrations.md)).
- [ ] إرجاع migration جدول `personal_access_tokens` (Sanctum) قبل ما تبدا في تسجيل الدخول.
- [ ] حذف `2026_11_23_191431_schema_tables.php`، لأنه فاضي.
- [ ] تعبئة الـ Models: `$primaryKey` و`$timestamps = false` و`$fillable` والعلاقات (شوف [04-models.md](04-models.md) و[08-relations.md](08-relations.md)).
- [ ] Seeder لأول مشرف، والبيانات من `.env`.
- [ ] تفعيل `extension=intl` في `C:\php\php.ini`.
- [ ] (اختياري) PostgreSQL ما يعملش index تلقائي لأعمدة الـ FK، فنضيفهم بعدين لتسريع البحث، ونضيف GIST index للـ location.
