# مستودع جمعية تحدي الإعاقة

نظام عربي متكامل لإدارة مخزون المواد الغذائية والاستهلاكية، مبني بـ Laravel وInertia وVue 3 وTailwind CSS، بواجهة RTL متجاوبة ومظهر فاتح/داكن وإشعارات فورية.

## المزايا

- إدارة المنتجات والأصناف مع رمز تلقائي لكل منتج.
- تسجيل حركات الإدخال والإخراج مع الكمية قبل/بعد والسبب والمستخدم.
- أسباب الحركة: الشركة، المطبخ، الإدارة، النادي، أخرى — صالحة للإدخال والإخراج.
- تتبع تاريخ صلاحية واحد لكل منتج وتنبيهات المخزون المنخفض.
- لوحة تحكم بتسعة مؤشرات رئيسية ورسوم بيانية وحركات حديثة.
- إشعارات داخل التطبيق مع جرس إشعارات وتحديث فوري عبر Reverb.
- تقارير جرد وحركات ومخزون منخفض وصلاحية وملخص أصناف.
- تنزيل التقارير بصيغة PDF عربية.
- جميع الحسابات لديها صلاحية كاملة لإدارة المخزون والإعدادات.
- إعدادات للنظام والعملة وحدود التنبيهات والمظهر والحساب الشخصي.

## التقنيات

- PHP 8.3 و Laravel 13
- Inertia.js و Vue 3
- Tailwind CSS 4 و Vite
- Laravel Reverb للإشعارات الفورية
- SQLite محليًا، مع إمكانية التحويل إلى MySQL
- mPDF لتوليد تقارير PDF عربية

## التشغيل

```bash
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

ثم شغّل بيئة التطوير الكاملة (الخادم + Vite + Reverb + الطابور):

```bash
composer dev
```

أو شغّل الخدمات يدويًا:

```bash
php artisan serve
npm run dev
php artisan reverb:start
php artisan queue:work
```

الرابط الافتراضي: `http://localhost:8000`. إذا كان المنفذ مشغولًا استخدم مثلًا:

```bash
php artisan serve --port=8001
```

### إعداد البيئة

تأكد من القيم التالية في `.env`:

```env
APP_URL=http://localhost:8000
APP_TIMEZONE=Africa/Casablanca
APP_LOCALE=ar
DB_CONNECTION=sqlite
BROADCAST_CONNECTION=reverb
MAIL_MAILER=log
```

وللإشعارات الفورية:

```env
REVERB_APP_ID=depot
REVERB_APP_KEY=depot-local-key
REVERB_APP_SECRET=depot-local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

عند استخدام MySQL، غيّر `DB_CONNECTION` وقيم `DB_*` ثم نفّذ `php artisan migrate --seed`. جميع الترحيلات متوافقة مع MySQL.

## الحسابات

لا يوجد تسجيل عام. تُنشأ الحسابات من الطرفية بالاسم فقط:

```bash
php artisan user:create
```

أو تمرير البيانات مباشرة:

```bash
php artisan user:create --name="سارة" --password="Password123"
```

لتغيير كلمة المرور:

```bash
php artisan user:password "سارة" --password="NewPassword123"
```

الأسماء فريدة، وكلمة المرور يجب أن تتكون من 8 أحرف على الأقل وتتضمن حرفًا ورقمًا.

### الحسابات التجريبية

بيانات العرض تُنشأ مع `php artisan migrate --seed`:

| الاسم | كلمة المرور |
| --- | --- |
| `سارة العتيبي` | `password` |
| `خالد الحربي` | `password` |

## الشعار

ضع شعار الجمعية في:

```text
public/images/logo.png
```

يدعم النظام أيضًا `logo.svg` و`logo.jpg` و`logo.jpeg`. في حال عدم وجود أي منها يظهر رمز بديل أنيق. يُستخدم الشعار في الواجهة ورأس تقارير PDF. بريد الجمعية الظاهر في رأس التقارير يُضبط من صفحة الإعدادات.

## التنبيهات

- الحد الافتراضي للمخزون قابل للتعديل من الإعدادات، مع إمكانية تجاوزه لكل منتج.
- مدة التنبيه قبل انتهاء الصلاحية قابلة للتعديل من الإعدادات.
- التنبيهات مخزنة داخل قاعدة البيانات وتظهر في الجرس وصفحة الإشعارات.
- تُرسل التنبيهات إلى جميع الحسابات، وتُبث فوريًا عبر Reverb.
- يُمنع تكرار التنبيه غير المقروء لنفس المنتج ونفس النوع، ويُغلق تلقائيًا عند زوال السبب.
- يمكن تشغيل الفحص يدويًا:

```bash
php artisan stock:check-alerts
```

- مجدول يوميًا الساعة 08:00 بتوقيت `APP_TIMEZONE`. شغّل المجدول في الإنتاج:

```bash
php artisan schedule:work
```

- إذا كان Reverb متوقفًا، تستمر حركات المخزون بشكل طبيعي وتظهر الإشعارات عند إعادة التشغيل أو عبر التحديث الدوري للصفحة.

## التقارير

- جرد المخزون
- حركات المخزون (مع فلترة الفترة)
- المخزون المنخفض
- تواريخ الصلاحية
- ملخص الأصناف

تتوفر جميع التقارير لجميع الحسابات للعرض والتنزيل بصيغة PDF.

## الاختبارات والجودة

```bash
php artisan test
npm run build
vendor/bin/pint --test
npm run format:check
```

## البنية المختصرة

- `app/Http/Controllers` — وحدات التحكم التي تُرجع صفحات Inertia.
- `app/Support/Present.php` — تحويل البيانات إلى خصائص جاهزة للواجهة.
- `app/Models` — المستخدمون والأصناف والمنتجات والحركات.
- `app/Services` — منطق المخزون والتنبيهات والتقارير.
- `app/Events/StockAlertCreated.php` — حدث البث الفوري للإشعارات.
- `resources/js/Pages` — صفحات Vue.
- `resources/js/Components` — مكونات الواجهة المشتركة.
- `resources/views/app.blade.php` — قالب Inertia الجذري.
- `resources/views/reports/pdf/document.blade.php` — قالب PDF.
- `database/seeders/DemoDataSeeder.php` — بيانات العرض.
- `tests/Feature` — اختبارات الصفحات والمصادقة والمخزون والتقارير.
