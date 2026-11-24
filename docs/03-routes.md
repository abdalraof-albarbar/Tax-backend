# 3. الـ Routes وتقسيمها

عندنا واجهتين، وكل وحدة ليها ملف routes:
- **Flutter** (تطبيق موبايل للمفتشين) ← `routes/flutter.php` ← الرابط يبدأ بـ `/api/flutter`
- **React** (لوحة للمشرفين) ← `routes/react.php` ← الرابط يبدأ بـ `/api/react`

## الربط في `app/Providers/RouteServiceProvider.php`
```php
Route::middleware('api')->prefix('api/flutter')
    ->group(base_path('routes/flutter.php'));

Route::middleware('api')->prefix('api/react')
    ->group(base_path('routes/react.php'));
```
- `prefix`: الكلمة اللي تنزاد قدام كل رابط في الملف.
- `middleware('api')`: بدون session ولا cookies. مناسب لتطبيقات تتعامل بالـ JSON.

مثال: `Route::get('visits', ...)` داخل `flutter.php` يصير رابطه `GET /api/flutter/visits`.

## شكل route بسيط يرجّع JSON
```php
Route::get('regions', function () {
    return Region::all();      // Laravel يحوّلها JSON تلقائياً
});
```

## CRUD كامل في سطر واحد (لما تستعمل Controller)
```powershell
php artisan make:controller RegionController --api
```
```php
Route::apiResource('regions', RegionController::class);
```
| Method | الرابط | الدالة |
|---|---|---|
| GET | /regions | index |
| POST | /regions | store |
| GET | /regions/{id} | show |
| PUT/PATCH | /regions/{id} | update |
| DELETE | /regions/{id} | destroy |

## تشوف كل الـ routes
```powershell
php artisan route:list
```

## localhost
`localhost:8000` رابط جهازك بس. على السيرفر يصير الدومين الحقيقي، والمسار بعده يقعد كما هو.
