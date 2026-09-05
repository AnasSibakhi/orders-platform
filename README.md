# منصة الطلبات متعددة القنوات (Multi-tenant Orders Platform)

لوحة تحكم موحّدة لتجّار يستقبلون طلبات من أكثر من قناة (واتساب، انستقرام،
متجر إلكتروني...) — بدل ما يضيع بينهم. كل تاجر ("Business") له مساحته
الخاصة: فريقه، عملاؤه، قنواته، طلباته، معزولة تمامًا عن أي تاجر آخر.

## ⚠️ ملاحظة حول بيئة التنفيذ

بُني هذا المشروع يدويًا في بيئة عمل بدون وصول لـPackagist (مصدر حزم
PHP)، لذلك لم أستطع تشغيل `composer install` أو `php artisan
migrate/test` فعليًا هنا. تحققت بدلاً من ذلك من:
- خلو كل ملفات PHP من أخطاء syntax (`php -l` على كل ملف)
- صحة بنية `composer.json`
- توازن كل Blade directives في كل الـviews
- تطابق كل أسماء الـroutes المستخدمة مع المعرّفة فعليًا
- **الأهم:** كتابة اختبارات صريحة لعزل البيانات بين التجّار
  (`tests/Feature/Tenancy/TenantIsolationTest.php`) تغطي بالضبط السيناريو
  اللي ممكن يسبب ثغرة أمنية حقيقية

**رجاءً شغّل `php artisan test` محليًا وأخبرني بالنتيجة** قبل أي اعتماد
إنتاجي فعلي.

## التشغيل — عن طريق Docker (الأسهل، أمر واحد)

```bash
docker compose up --build
```

يجهز كل شي تلقائيًا: PHP + MySQL + Redis + تثبيت المكتبات + migrations +
seed. افتح `http://localhost:8000`.

## النشر على Vercel

Vercel أضافت (أغسطس 2026) طريقة رسمية لتشغيل PHP كـ Container حقيقي
(FrankenPHP)، بدل الطريقة القديمة المقيّدة (serverless function بنظام
ملفات للقراءة فقط). هذا المشروع مجهّز لها عبر 3 ملفات: `Dockerfile.vercel`,
`Caddyfile`, `vercel.json`.

⚠️ **لم أستطع تشغيل `docker build` فعليًا هنا للتحقق** (لا يوجد Docker
daemon ولا وصول لـPackagist في بيئتي) — تحقّقت فقط من صحة صياغة كل ملف
(JSON صحيح، PHP بدون أخطاء). جرّب البناء محليًا أول (`docker build -f
Dockerfile.vercel .`) قبل الاعتماد عليه، أو راقب لوق البناء (Build Logs)
بلوحة Vercel وأخبرني بأي خطأ يطلع.

### الخطوات

1. **قاعدة بيانات MySQL خارجية** — Vercel ما يوفر MySQL مُدار. استخدم
   خدمة خارجية مجانية للتجربة (مثل Railway أو PlanetScale)، واحصل على
   `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
2. **Redis** — من [Vercel Marketplace](https://vercel.com/marketplace)
   (تكامل Redis)، أو خدمة خارجية.
3. في إعدادات مشروع Vercel، غيّر **Framework Preset من "Vite" إلى "Other"**
   (Vite Preset غلط لهذا المشروع — يبني الواجهة بس بدون تشغيل PHP).
4. أضف Environment Variables بلوحة Vercel (Project Settings → Environment
   Variables):
   ```
   APP_KEY=<شغّل: php artisan key:generate --show، محليًا>
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=...
   DB_PORT=3306
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...
   SESSION_DRIVER=redis
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   REDIS_HOST=...
   REDIS_PORT=...
   REDIS_PASSWORD=...
   ```
5. اضغط **Deploy**.
6. بعد أول نشر ناجح، شغّل الـmigrations يدويًا (Vercel ما يشغّل أوامر
   artisan تلقائيًا): إما عبر `vercel exec` من جهازك، أو أضف route مؤقت
   محمي بتوكن سري يشغّل `Artisan::call('migrate', ['--force' => true])`
   وتحذفه بعد الاستخدام.

### قيود حقيقية يجب معرفتها (نفس الكلام اللي قلته لك قبل، ينطبق هنا أيضًا)

- **Queue workers لا تشتغل بشكل دائم على Vercel** (الحاويات تتوقف عند
  عدم النشاط). أي معالجة خلفية مستقبلية (مثل استقبال Webhooks من
  WhatsApp/Instagram وقت ما تُبنى) تحتاج [Vercel Cron](https://vercel.com/docs/cron-jobs)
  يستدعي endpoint بدل `php artisan queue:work` دائم التشغيل.
- **لا Scheduler دائم** لنفس السبب — أي مهمة مجدولة تحتاج Vercel Cron.
- **نظام الملفات غير دائم بين عمليات النشر** (يُعاد تجهيزه من الصورة كل
  مرة) — أي رفع ملفات (صور منتجات مستقبلًا مثلاً) يحتاج
  [Vercel Blob](https://vercel.com/docs/vercel-blob) بدل التخزين المحلي.

هذي القيود لا تمنع المشروع الحالي من الشغل (لسا ما بنينا أي معالجة خلفية
حقيقية)، لكن خذها بعين الاعتبار قبل ما تبني عليها لاحقًا.

## التشغيل محليًا (بدون Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate

# عدّل .env: DB_* حسب MySQL لديك، وحدّد ADMIN_EMAIL/ADMIN_PASSWORD

php artisan migrate
php artisan db:seed

npm install && npm run build
php artisan serve
```

### تشغيل الاختبارات

```bash
php artisan test
```

أهم ملف اختبار في المشروع: `tests/Feature/Tenancy/TenantIsolationTest.php`
— يتحقق تحديدًا إن تاجر ما يقدر يوصل لبيانات تاجر ثاني، حتى لو خمّن رقم
الطلب مباشرة بالرابط.

## أول استخدام

1. سجّل حساب جديد (`/register`) — أي مستخدم جديد ما يملك أي نشاط تجاري
   بعد، فيُحوَّل تلقائيًا لصفحة "إنشاء نشاط تجاري".
2. سوّي أول نشاط تجاري — تصير **owner** فيه تلقائيًا.
3. من لوحة التحكم تقدر: تضيف قنوات (Phase لاحقة للربط الفعلي)، تشوف
   الطلبات والعملاء (تُضاف يدويًا حاليًا أو عبر seed)، تضيف أعضاء فريق
   (لازم يكون عندهم حساب مسجّل بالمنصة مسبقًا).

## بنية المشروع

```
app/
├── Support/Tenancy/CurrentBusiness.php   # مين هو التاجر الحالي لهذا الـrequest
├── Models/Concerns/BelongsToBusiness*    # عزل بيانات كل تاجر (راجع docs/ARCHITECTURE.md)
├── Http/Middleware/IdentifyBusiness.php  # التحقق من العضوية + تفعيل السياق
├── Http/Middleware/EnsureBusinessRole.php# owner/manager/agent
├── Http/Controllers/Business/            # Dashboard, Orders, Customers, Channels, Team
├── Http/Controllers/Auth/                # تسجيل دخول/خروج/تسجيل/استعادة كلمة مرور
└── Models/                               # Business, TeamMember, Channel, Customer, Order...

database/
├── migrations/                           # users++, businesses, team_members, orders, ...
└── seeders/                              # صلاحيات المنصة + Super Admin (اختياري)

resources/views/
├── onboarding/create-business.blade.php
├── business/{switch,dashboard,team}.blade.php
├── orders/{index,show}.blade.php
├── customers/{index,show}.blade.php
└── channels/index.blade.php

tests/Feature/
├── Tenancy/TenantIsolationTest.php       # ← الأهم، راجعه أول شي
├── Business/{BusinessOnboardingTest,TeamManagementTest,OrderManagementTest}.php
├── Auth/*.php
├── HomeRedirectTest.php
└── RolesAndPermissionsTest.php           # أدوار موظفي المنصة (منفصلة عن TeamMember)
```

## التوثيق

- `docs/ARCHITECTURE.md` — **اقرأه أول شي**: يشرح بالتفصيل مشكلة أمان حقيقية
  اكتُشفت وصُلِّحت أثناء البناء (تسرّب بيانات محتمل بسبب توقيت تنفيذ
  الـmiddleware)، وليش الحل المُطبَّق بثلاث طبقات دفاع لا طبقة واحدة.
- `docs/ERD.md` — مخطط قاعدة البيانات الكامل.

## ما تبقّى (غير مبني بعد، متعمّد)

استقبال Webhooks فعلي من WhatsApp Cloud API / Instagram Graph API، مطابقة
تلقائية للعملاء من الرسائل الواردة، Notifications، Analytics/Caching
للوحة التحكم، Broadcasting الفوري (Laravel Reverb)، اختبارات الأداء
(Load testing).
