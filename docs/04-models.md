# 4. الـ Models

الموديل كلاس يمثّل جدول. من خلاله تقرا وتكتب في الجدول بدون SQL.

## كيف الموديل يعرف جدوله؟
Laravel يحوّل اسم الكلاس لصيغة الجمع بحروف صغيرة و `_`:
| Model | الجدول |
|---|---|
| `User` | `users` |
| `TaxDeclaration` | `tax_declarations` |
| `ReportPhoto` | `report_photos` |

لو الاسم ما يمشيش على القاعدة: `protected $table = 'اسم_الجدول';`

## المفتاح الأساسي لو مش `id`
```php
protected $primaryKey = 'employee_no';  // اسم العمود
public $incrementing = false;           // مش رقم يزيد تلقائياً
protected $keyType = 'string';          // نوعه نص
```
ولما يكون رقم تلقائي لكن باسم ثاني (مثل `region_id`)، تكفي `$primaryKey`.

مثال: `Employee` مفتاحه `employee_no` (نص)، فيحتاج الثلاث سطور.

## الـ Models توا
`User` و`Employee` (بدل `Inspector` و`Supervisor`) و`Region` و`Business` و`TaxDeclaration` و`Visit`
و`Report` و`ReportPhoto` و`ReportDocument` و`ViolationType`. `report_violations` ما عنده موديل.
`employees` فيه `timestamps()`، فـ `Employee` **ما يحتاجش** `$timestamps = false`، والباقين يحتاجوه.

## بدون timestamps
الجداول عندنا ما فيهاش `created_at` و`updated_at`، فلازم:
```php
public $timestamps = false;
```
لو ما حطيتهاش، Laravel يحاول يعبّيهم ويطلع خطأ.

## `$fillable`
الأعمدة المسموح تتعبّى مرة وحدة بـ `create([...])` أو `update([...])`.
أي عمود مش في القائمة يتجاهله Laravel. هذي حماية اسمها **mass assignment**.
- `password` تقعد في `fillable`، لأن المستخدم يحتاج يحطها.
- `role` **ما نحطوهاش**، عشان ما حد يبعت `"role":"supervisor"` ويرقّي روحه. نعطيها قيمة في الكود:
```php
$user = new User($request->only('name', 'email', 'password'));
$user->role = 'inspector';
$user->save();
```

## `$hidden`
أعمدة ما تطلعش في الـ JSON. مثال: `password`.

## `$casts`
تحويل تلقائي للقيمة:
```php
'password' => 'hashed',   // تتشفّر تلقائياً قبل الحفظ
```

## الـ traits في User
| Trait | شن يدير | محتاجينه؟ |
|---|---|---|
| `HasApiTokens` (Sanctum) | يعطي كل مستخدم توكن بعد تسجيل الدخول. Flutter وReact يبعتوه في الهيدر: `Authorization: Bearer <token>` | نعم، لكن يحتاج جدول `personal_access_tokens` |
| `Notifiable` | إرسال إشعارات (إيميل، SMS...) | لا حالياً |
| `remember_token` | خاصية "تذكرني" في تسجيل الدخول بالـ session (للويب) | لا، حذفناه |

## موديل `User` الحالي باختصار
```php
use HasApiTokens;
public $timestamps = false;
protected $fillable = ['name', 'email', 'password'];
protected $hidden = ['password'];
protected $casts = ['password' => 'hashed'];
```
