# Architecture — منصة الطلبات متعددة القنوات

## الفكرة
منصة SaaS متعددة المستأجرين (Multi-tenant): كل تاجر ("Business") يملك
مساحته الخاصة — فريقه، عملاؤه، قنواته، طلباته — معزولة تمامًا عن أي تاجر
آخر يستخدم نفس المنصة.

```
Webhook من قناة خارجية (لاحقًا: WhatsApp Cloud API, Instagram Graph API)
        ↓
   Controller خفيف (يتحقق من التوقيع، يرجّع 200 فورًا)
        ↓
   Job على Redis Queue (idempotent عبر webhook_events.external_event_id)
        ↓
   ينشئ/يطابق Customer، ينشئ Order، يُطلق Event
        ↓
   Dashboard يتحدّث (Broadcasting لاحقًا)
```

هذا الجزء (استقبال الـwebhooks الفعلي) خارج نطاق ما بُني حاليًا — الجداول
والعلاقات جاهزة له (`webhook_events`)، لكن endpoints الاستقبال الفعلية
والربط بـWhatsApp/Instagram تأتي في مرحلة لاحقة.

## أهم قرار في المشروع: كيف نضمن عدم تسرّب بيانات تاجر لتاجر آخر

هذا القرار يستحق شرحًا مفصلًا لأنه أخطر نقطة فشل ممكنة في أي SaaS متعدد
المستأجرين — خطأ برمجي بسيط هنا يعني عميل يشوف بيانات عميل ثاني.

### المشكلة التقنية الدقيقة التي واجهناها
الحل "الساذج" هو: كل Model عليه Global Scope يفلتر تلقائيًا حسب
`business_id` الحالي، ثم نعتمد على Laravel's Route Model Binding
(`Route::get('/orders/{order}')` مع `Order $order` في الـController) ليجيب
الطلب المطلوب — والـScope يمنع تلقائيًا جلب طلب تاجر ثاني.

**المشكلة:** Route Model Binding في Laravel يُنفَّذ عبر middleware اسمه
`SubstituteBindings`، وهو **جزء من الـmiddleware الافتراضي (`web` group)
الذي ينفَّذ قبل أي middleware مخصص نضيفه على الـroute** (مثل
`IdentifyBusiness` اللي يحدد التاجر الحالي ويفعّل الـScope). يعني: وقت ما
Laravel يحاول يجيب الـ`Order` تلقائيًا، الـScope **لسا مو مفعّل بعد** —
فالاستعلام يرجع أي طلب بغض النظر عن صاحبه. هذا خطأ *توقيت* دقيق جدًا،
يسهل جدًا الوقوع فيه دون ملاحظة، ولا يظهر إلا لو كتبت اختبار يحاكي تاجر
يحاول يوصل لبيانات تاجر ثاني تحديدًا.

### الحل المُطبَّق (ثلاث طبقات، لا طبقة واحدة)

1. **Global Scope** (`BelongsToBusinessScope`) — يبقى موجود كخط دفاع عام
   لأي استعلام قائمة (`Order::all()`, `->paginate()`...) يُنفَّذ *داخل*
   الـcontroller method بعد ما الـmiddleware كله خلص —وهذا الاستخدام آمن
   100% لأن توقيته صحيح هناك.
2. **Middleware صريح** (`IdentifyBusiness`) يتحقق من عضوية المستخدم في
   الـbusiness عبر استعلام مباشر على `team_members` (لا يعتمد على أي
   Scope)، ويُرجع 404 لأي شخص مو عضو.
3. **جلب صريح عبر العلاقة** لأي مورد فردي (Order, Customer):
   ```php
   $order = $business->orders()->findOrFail($order); // $order هنا int عادي
   ```
   بدل:
   ```php
   public function show(Business $business, Order $order) // ❌ خطر التوقيت أعلاه
   ```
   العلاقة `$business->orders()` تضيف `where('business_id', $business->id)`
   بشكل صريح كجزء من تعريف العلاقة نفسها — لا علاقة له بتوقيت أي Scope أو
   Middleware، فهو صحيح دائمًا بغض النظر عن ترتيب التنفيذ.

**الدرس العام:** لا تعتمد على Global Scope وحده لعزل بيانات حساس في نظام
متعدد المستأجرين — استخدمه كخط دفاع إضافي، لكن اجعل كل استعلام لمورد فردي
صريحًا عبر علاقة الـtenant. راجع `tests/Feature/Tenancy/TenantIsolationTest.php`
لاختبارات تتحقق من هذا تحديدًا، بما فيها محاولة تاجر "يخمّن" رقم طلب تاجر
آخر.

## الأدوار: مستويان منفصلان تمامًا

| | `roles` / `permissions` (عام) | `team_members.role` (لكل business) |
|---|---|---|
| لمين | موظفي المنصة نفسها (أنت/فريق الدعم) | عملاء المنصة (كل تاجر وفريقه) |
| القيم | `super_admin`, `support` | `owner`, `manager`, `agent` |
| النطاق | كل الأنشطة التجارية على المنصة | نشاط تجاري واحد فقط |
| كيف يُمنح | يدويًا من قِبَلك، نادرًا | تلقائي عند إنشاء business (owner)، أو دعوة (manager/agent) |

هذا الفصل يمنع خلطًا خطيرًا: عميل عادي (تاجر) **لا يملك أي صف في
`roles`**، فحتى لو حصل خطأ برمجي بمكان ما وحاول أحد التحقق من صلاحية عبر
`hasRole()` بدل `TeamMember::role`، النتيجة الافتراضية آمنة (false)، لا
صلاحية زائدة بالخطأ.

## طبقات الكود

```
app/
├── Support/Tenancy/CurrentBusiness.php   # Singleton: التاجر الحالي لهذا الـrequest
├── Models/Concerns/
│   ├── BelongsToBusiness.php             # trait: يفعّل الـScope + auto-stamp
│   └── BelongsToBusinessScope.php        # الـGlobal Scope نفسه
├── Http/Middleware/
│   ├── IdentifyBusiness.php              # يتحقق من العضوية + يفعّل CurrentBusiness
│   └── EnsureBusinessRole.php            # owner/manager/agent authorization
├── Http/Controllers/Business/            # كل الـcontrollers الخاصة بالـtenant
├── Models/Business.php, TeamMember.php   # جذر الـtenancy
└── Models/{Channel,Customer,Order,...}   # tenant-scoped models (تستخدم BelongsToBusiness)
```

## ما هو مبني الآن، وما تبقّى

**مبني ويعمل:** Multi-tenancy الكامل، Auth، الفريق (team management)،
CRUD للطلبات/العملاء/القنوات (يدوي)، عزل بيانات مُختبَر بشكل صريح.

**غير مبني بعد (متعمّد):** استقبال webhooks فعلي من WhatsApp/Instagram،
مطابقة تلقائية للعملاء من الرسائل الواردة، أي واجهة API خارجية،
Notifications، Analytics/Caching للوحة التحكم، Broadcasting الفوري.
