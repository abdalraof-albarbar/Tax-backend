# 9. كتابة SQL بإيدك (custom SQL)

نعم، تقدر تكتب SQL مباشرة داخل Laravel.

| الطريقة | الاستعمال | يرجّع |
|---|---|---|
| `DB::select($sql, [...])` | SELECT | array من النتائج |
| `DB::insert` / `DB::update` / `DB::delete` | تعديل بيانات | عدد الصفوف أو true |
| `DB::statement($sql)` | أي أمر (CREATE INDEX، INSERT...) | true/false |
| `DB::unprepared($sql)` | عدة أوامر مع بعض، بدون `?` | true/false |

```php
use Illuminate\Support\Facades\DB;

$visits = DB::select(
    'SELECT * FROM visits WHERE inspector_no = ? AND status = ?',
    [$employeeNo, 'pending']
);
```

## ⚠️ دايماً استعمل `?`
لا تحط قيمة جاية من المستخدم داخل نص الـ SQL مباشرة (SQL injection).
```php
// غلط
DB::select("SELECT * FROM users WHERE email = '$email'");
// صح
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

## خلط SQL مع Eloquent
```php
Report::whereRaw('ST_DWithin(location, ST_MakePoint(?, ?)::geography, ?)', [$lng, $lat, 500])->get();
Visit::selectRaw('status, count(*) as total')->groupBy('status')->get();
```

## Transaction
يا ينجحوا الكل يا ما يصير شي:
```php
DB::transaction(function () {
    DB::insert(...);
    DB::insert(...);
});
```

## SQL داخل migration
```php
public function up(): void
{
    DB::statement('CREATE INDEX reports_location_gist ON reports USING GIST (location)');
}
```

## التجربة في `routes/api.php`
فيه route معلّق فيه `INSERT` بـ `DB::statement`. لو فعّلته:
- استعمل `?` بدل القيم المكتوبة.
- الكلمة السرية لازم تتشفّر (`Hash::make`)، لأن `DB::statement` ما يستعملش `casts` الموديل.
