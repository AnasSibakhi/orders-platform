# ERD — منصة الطلبات متعددة القنوات

```mermaid
erDiagram
    USERS ||--o{ TEAM_MEMBERS : has
    BUSINESSES ||--o{ TEAM_MEMBERS : has
    BUSINESSES ||--o{ CHANNELS : owns
    BUSINESSES ||--o{ CUSTOMERS : owns
    BUSINESSES ||--o{ ORDERS : owns
    CHANNELS ||--o{ CUSTOMER_CHANNEL_IDENTITIES : links
    CUSTOMERS ||--o{ CUSTOMER_CHANNEL_IDENTITIES : has
    CUSTOMERS ||--o{ ORDERS : places
    CHANNELS ||--o{ ORDERS : source
    ORDERS ||--o{ ORDER_ITEMS : contains
    ORDERS ||--o{ ORDER_STATUS_HISTORY : tracks
    CHANNELS ||--o{ WEBHOOK_EVENTS : receives
    USERS ||--o{ ORDERS : "assigned to"

    USERS {
        bigint id PK
        string name
        string email UK
        string status "active|disabled"
        timestamp deleted_at
    }
    BUSINESSES {
        bigint id PK
        string name
        string slug UK
        string timezone
        string status "active|suspended"
        timestamp deleted_at
    }
    TEAM_MEMBERS {
        bigint id PK
        bigint business_id FK
        bigint user_id FK
        string role "owner|manager|agent"
        string status "invited|active|disabled"
    }
    CHANNELS {
        bigint id PK
        bigint business_id FK
        string type "whatsapp|instagram|store_webhook|email"
        string name
        string status "connected|disconnected|error"
        text credentials_encrypted
    }
    CUSTOMERS {
        bigint id PK
        bigint business_id FK
        string name
        string phone_normalized
        string email
    }
    CUSTOMER_CHANNEL_IDENTITIES {
        bigint id PK
        bigint customer_id FK
        bigint channel_id FK
        string external_id
    }
    ORDERS {
        bigint id PK
        bigint business_id FK
        bigint customer_id FK
        bigint channel_id FK
        bigint assigned_to_user_id FK
        string status "new|processing|paid|shipped|completed|cancelled"
        decimal total
        string currency
        string external_order_id
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        string product_name
        int quantity
        decimal price
    }
    ORDER_STATUS_HISTORY {
        bigint id PK
        bigint order_id FK
        string from_status
        string to_status
        bigint changed_by_user_id FK
    }
    WEBHOOK_EVENTS {
        bigint id PK
        bigint channel_id FK
        string external_event_id UK
        string status "pending|processed|failed"
        json payload
    }
```

## القرار المعماري الأهم: كيف يتم عزل بيانات كل تاجر

كل جدول تجاري (Channel, Customer, Order, OrderItem, OrderStatusHistory,
WebhookEvent) يحمل `business_id`، ويُمنع الوصول إليه بثلاث طبقات مستقلة —
عمدًا مكرّرة، لأن نظام SaaS متعدد المستأجرين لا يحتمل نقطة فشل واحدة هنا:

1. **Global Scope تلقائي** (`BelongsToBusinessScope`) على أي Eloquent query
   عادية — يمنع التسريب في أي كود مستقبلي ينسى إضافة `where business_id`.
2. **Middleware يتحقق من العضوية** (`IdentifyBusiness`) قبل الدخول لأي
   route تحت `/b/{business}/...` — يرجع 404 (لا 403) لأي شخص مو عضو، حتى لا
   يكشف حتى وجود الـbusiness.
3. **جلب صريح عبر العلاقة** (`$business->orders()->findOrFail($id)`) لأي
   مورد فرعي (Order, Customer) بدل الاعتماد على Route Model Binding
   المباشر — لأن توقيت تنفيذ الـMiddleware في Laravel لا يضمن أن الـScope
   مفعّل وقت حل الـRoute Model Binding. راجع `docs/ARCHITECTURE.md` لتفصيل
   هذه النقطة تحديدًا، لأنها أهم قرار أمني في المشروع.

## ملاحظات تصميم إضافية

- **`role` على `team_members` نص بسيط (owner/manager/agent) لا جدول
  Roles منفصل**: عمدًا. الأدوار داخل نشاط تجاري بسيطة وثابتة (3 قيم فقط)،
  وربطها بجدول `roles` العام (الخاص بموظفي المنصة نفسها) كان سيخلط بين
  مفهومين مختلفين تمامًا — راجع الفقرة التالية.
- **جدول `roles`/`permissions` العام منفصل تمامًا عن `team_members`**:
  `roles` يمثّل *موظفي المنصة نفسها* (`super_admin`, `support`) الذين
  يديرون كل الأنشطة التجارية، بينما `team_members.role` يمثّل *صلاحية
  المستخدم داخل نشاط تجاري واحد بالتحديد*. عميل عادي للمنصة لا يملك أي صف
  في `roles` إطلاقًا.
- **`order_status_history` بلا `updated_at`**: سجل تاريخي لا يُعدَّل أبدًا
  بعد إنشائه، فلا حاجة لعمود تحديث.
- **`customer_channel_identities` منفصل عن `customers`**: عميل واحد قد
  يتواصل عبر أكثر من قناة (واتساب + انستقرام) بمعرّفات خارجية مختلفة —
  الجدول هذا يربطهم لنفس سجل العميل الداخلي.
