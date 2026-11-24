# 2. هيكلية مشروع Laravel

```
Tax-backend/
├── app/
│   ├── Http/Controllers/   ← الكنترولرز (منطق كل طلب)
│   ├── Http/Kernel.php     ← الـ middleware
│   ├── Models/             ← موديل لكل جدول
│   └── Providers/
│       └── RouteServiceProvider.php ← يربط ملفات الـ routes بالـ prefix
├── config/                 ← الإعدادات (تقرأ من .env)
├── database/
│   ├── migrations/         ← إنشاء وتعديل الجداول
│   └── seeders/            ← بيانات أولية (مثل أول مشرف)
├── routes/
│   ├── api.php             ← /api/...
│   ├── flutter.php         ← /api/flutter/...  (تطبيق المفتشين)
│   ├── react.php           ← /api/react/...    (لوحة المشرفين)
│   └── web.php
├── docs/                   ← هذي المذكرات
├── .env                    ← الأسرار والاتصال بالداتا بيس (ما يترفعش على git)
└── composer.json           ← الحزم ونسخة PHP المطلوبة
```

## مسار الطلب (request)
```
Flutter / React
     │  GET /api/flutter/...
     ▼
routes/flutter.php  →  Controller  →  Model  →  PostgreSQL
     ▲                                              │
     └──────────────── JSON response ◄──────────────┘
```

## أمر مفيد
```powershell
php artisan about
```
يوريك نسخة Laravel وPHP ونوع الداتا بيس وإعدادات المشروع.
