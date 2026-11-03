# وحدة الإشارات — Signals Module

> توثيق تقني ثنائي اللغة (عربي + مصطلحات إنجليزية) موجّه للمطورين.
> آخر مراجعة ضد الكود: أكتوبر 2026 — اللوثج الرئيسي: `app/Http/Controllers/SignalController.php` (315 سطر).
> الوثيقة الشريكة: [وحدة البلاغات](reports-module.md).

---

## 1. نظرة عامة / Overview

وحدة الإشارات تدير **سلاسل تبادل الإشارات (Threads)** بين الجهات: مستخدم يسجّل ثريدًا (`MainSignalTBL`) يحوي كروتًا (Units) — الإشارة الأساسية + ردود — ولكل كرت قائمة حالات افتراض (`Correct` / `X`) على جهات محددة. تشمل إعدادات: جهات الإشارة، مجموعات الجهات، مضمون الإشارة المسبق.

**نموذج "الثريد" هو الفارق الجوهري عن البلاغات:** لا حالة سير عمل (workflow) ولا قفل — التعديل يعيد بناء الثريد كاملًا.

**مسار الكود الرئيسي:**

```
routes/web.php (123-136, 326-355, 374-387)   → مسارات الموديول
app/Http/Controllers/
  SignalController.php                        → ثريد + كروت + تصدير (315 سطر)
  SignalAuthorityController.php               → إعدادات جهات الإشارة (56 سطر)
  SignalAuthorityGroupController.php          → إعدادات المجموعات (69 سطر)
  SignalContentController.php                 → إعدادات مضمون الإشارة (56 سطر)
app/Models/{MainSignal, SignalUnit, SignalAuth,
            SignalAuthority, SignalAuthorityGroup, SignalContent}.php
app/Exports/SignalsExport.php                 → تصدير Excel
resources/views/signals/*                     → الشاشات الرئيسية
resources/views/settings/signal-*/*           → شاشات الإعدادات
```

---

## 2. مخطط قاعدة البيانات / Database Schema

### 2.1 جداول المسار النشط (Active Path)

#### `MainSignalTBL` — جذر الثريد

Migration: `2026_01_01_000006_create_mainsignaltbl_table.php` — Model: `app/Models/MainSignal.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `MainSignalID` | bigIncrements | PK |
| `MainSignalCode` | string(500), nullable | كود الثريد `SIGNAL-{n}` — يُحسب بـ `buildNextSignalCode()` |
| `RECEIVER_ID` | unsignedBigInteger, nullable | المستخدم الذي أنشأ/استلم الثريد → `users.id` |

بدون `$timestamps`.

#### `SIGNAL_UNIT` — كروت الثريد (الإشارة الأساسية + الردود)

Migration: `2026_01_01_000024_create_signal_unit_table.php` + تغييرات لاحقة (§2.2) — Model: `app/Models/SignalUnit.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `ID` | bigint PK (رغم التعديلات — انظر §12) | أضافه migration: `2026_08_05_093009_add_primary_key_to_signal_unit_table.php` |
| `UNIT_SIGNAL_DATE` | date, nullable | تاريخ الكارت |
| `UNIT_SIGNAL_TIME` | time, nullable | وقت الكارت |
| `UNIT_SIGNAL_SUBJECT` | text, nullable | الموضوع |
| `UNIT_SIGNAL_CONTENT` | text, nullable | المضمون — **مطلوب** |
| `UNIT_AUTHORITY_ID` | unsignedBigInteger, nullable | جهة الإرسال → `SIGNAL_AUTHORITY.ID` |
| `MAIN_SEND_ID` | unsignedBigInteger, nullable | → `MainSignalTBL.MainSignalID` (جذر الثريد) |
| `MAIN_SEND_CODE` | string(50), nullable | كود الثريد (مكرر مع الجذر — dual key) |
| `UNIT_SIGNAL_TYPE` | string(50), nullable | `إشارة لاسلكية` أو `رصد مرئي` فقط (مقيّد في Validation) |

#### `SIGNAL_AUTH` — حالات الجهات لكل كرت

Migration: `2026_01_01_000022_create_signal_auth_table.php` — Model: `app/Models/SignalAuth.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `ID` | bigIncrements | PK |
| `CONTACT_NAME` | string(500), nullable | **اسم الجهة نصًا** (ليس مفتاحًا) |
| `STATE` | string(50), nullable | `Correct` أو `X` أو فارغ |
| `SIGNAL_ID` | unsignedBigInteger, nullable | **يشاور على `SIGNAL_UNIT.ID`** وليس على ثريد (تعليق صريح في `SignalAuth.php:24`) |
| `MAIN_SIGNAL_ID` | unsignedBigInteger, nullable | → جذر الثريد (للحذف الواسع) |
| `MAIN_SEND_CODE`/`MAIN_SIGNAL_CODE` | string(50), nullable | كود الثريد — يُستخدم في where مزدوج |

#### `SIGNAL_AUTHORITY` — جهات الإشارة (المرسل/المستقبل)

Migration: `2026_01_01_000008_create_signal_authority_table.php` — Model: `app/Models/SignalAuthority.php`

| العمود | النوع |
|---|---|
| `ID` | increments (PK) |
| `SIGNAL_NAME` | string(50), nullable |

#### `SIGNAL_AUTHORITY_GROUP` + `SIGNAL_AUTHORITY_GROUP_MEMBER` — مجموعات الجهات

Migration: `2026_10_01_000100_create_signal_authority_group_tables.php` — Models: `SignalAuthorityGroup`, العلاقة `belongsToMany`.

| الجدول | الأعمدة |
|---|---|
| `SIGNAL_AUTHORITY_GROUP` | `ID` increments PK، `GROUP_NAME` string(100) |
| `SIGNAL_AUTHORITY_GROUP_MEMBER` | `ID`، `GROUP_ID` + `AUTHORITY_ID` (unsigned int) + **unique** (`GROUP_ID`,`AUTHORITY_ID`) + index على `AUTHORITY_ID` + **FK حقيقيان** مع `onDelete cascade` |

هذا هو الجدول الوحيد في الموديول ذو FKs وunique فعليين.

#### `SIGNAL_CONTENT` — مضمون الإشارة المسبق

Migration: `2026_01_01_000009_create_signal_content_table.php` — Model: `SignalContent`

| العمود | النوع |
|---|---|
| `SIGNALCONTENT_ID` | bigIncrements PK |
| `SIGNALCONTENT` | string(500) |

### 2.2 تاريخ تعديلات `SIGNAL_UNIT` (مهم للصيانة)

1. `2026_01_01_000024_create_signal_unit_table.php`: الجدول أُنشئ **بدون PK** (التعليق: الجدول الأصلي في SQL Server كان بلا primary key) — `ID` كان `unsignedBigInteger` عاديًا.
2. `2026_08_05_093009_add_primary_key_to_signal_unit_table.php`: أضاف PK + auto-increment عبر raw SQL (`MODIFY ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`).
3. `2026_09_28_122211_allow_duplicate_id_in_unit_signal.php`: **عكس** ذلك: أزال auto-increment ثم PK ثم أضاف index عادي — بهدف السماح بتكرار `ID`. ⚠️ يستخدم اسم جدول `unit_signal` (حروف صغيرة) بينما الجدول الفعلي `SIGNAL_UNIT` — انظر §12.

### 2.3 جداول قديمة غير مستخدمة في المسار النشط (Legacy)

| الجدول | Migration | الملاحظات |
|---|---|---|
| `RECIEVE_SIGNAL` | `2026_01_01_000007_*` | Model `RecieveSignal` — **مستورد فقط** في `DashboardController.php:6` ولا يُستخدم (import ميت) |
| `SEND_SIGNAL` | `2026_01_01_000023_*` | Model `SendSignal` — لا استدعاءات له في أي controller |
| `REC_SIGNAL_AUTH` | `2026_01_01_000021_*` | Model `RecSignalAuth` — تابع لمسار الاستقبال القديم |

> لا تحذفها قبل التأكد من بيانات legacy المُرحّلة من SQL Server، لكن **لا تعتمد عليها في أي كود جديد** — المسار النشط هو `MainSignal` + `SignalUnit` + `SignalAuth`.

### 2.4 علاقات Model ER

```
MainSignal (MainSignalTBL) ─┬─ receiver()   belongsTo User         عبر RECEIVER_ID → id
                            └─ units()      hasMany SignalUnit     عبر MAIN_SEND_ID → MainSignalID

SignalUnit (SIGNAL_UNIT) ───┬─ sender()     belongsTo SignalAuthority  عبر UNIT_AUTHORITY_ID → ID
                            ├─ mainSignal() belongsTo MainSignal       عبر MAIN_SEND_ID → MainSignalID
                            └─ authStates() hasMany SignalAuth         عبر SIGNAL_ID → ID   ⚠️

SignalAuth (SIGNAL_AUTH) ───┬─ signalUnit() belongsTo SignalUnit   عبر SIGNAL_ID → ID (المسار الجديد فقط)
                            └─ mainSignal() belongsTo MainSignal   عبر MAIN_SIGNAL_ID → MainSignalID

SignalAuthority ─ groups() belongsToMany SignalAuthorityGroup
                  عبر pivot SIGNAL_AUTHORITY_GROUP_MEMBER (AUTHORITY_ID ⇄ GROUP_ID)
```

⚠️ `SignalAuth.SIGNAL_ID` له معنيان تاريخيان: في المسار النشط يشير إلى `SIGNAL_UNIT.ID` (موثّق في `app/Models/SignalAuth.php:24`)، بينما النماذج القديمة تستخدمه كـ `RECIEVE_SIGNAL.ID`.

---

## 3. دورة حياة الإشارة / Lifecycle & States

**لا يوجد Status field ولا Lock ولا workflow** — الفرق الجوهري عن البلاغات:

| البُعد | البلاغات | الإشارات |
|---|---|---|
| الحالة | `REQUEST_STATUS` (4 حالات نصية) | **لا يوجد** — فقط `SignalAuth.STATE` لكل جهة لكل كرت |
| القفل | `تم الانتهاء` + `LOCKED_BY` | **لا يوجد** — أي صاحب صلاحية يعدّل/يحذف |
| التعديل | حقول محددة in-place | **إعادة بناء كاملة**: حذف كل الكروت + حالاتها وإعادة إنشائها |
| الترقيم | `REP-{n}` | `SIGNAL-{n}` |

**`SignalAuth.STATE` — نظام الافتراض (Ack/Nack):**

- لكل كرت × جهة: `Correct` (أخضر ✓) أو `X` (أحمر ✗) أو لم تُحدد (طƊ عادٍ).
- التبديل في الواجهة يتدرّج: `'' → 'Correct' → 'X' → ''` (JS في `create.blade.php`).
- الحالة تُخزَّن كاسم الجهة نصًا في `CONTACT_NAME` — لا FK.
- اختيار "مجموعة" يطبّق حالتها على كل الأعضاء ويحفظ حالة المجموعة نفسها (متعدد الألوان: مختلط/أخضر/أحمر/كهرماني) ثم يزامن الفردية.

**التدفق الكامل عند الإنشاء (`store()`):**

```
1. buildNextSignalCode() ──► SIGNAL-{n+1}
2. Transaction:
   ├─ MainSignal::create(code, RECEIVER_ID=auth()->id())
   └─ saveUnits(signals[], mainSignal):
        لكل كارت: SignalUnit::create(...)
        └─ لكل جهة لها state غير فارغ: SignalAuth::create(...)
3. logSystemRecord(...) ──► SystemRecord
4. redirect → signals.create بنجاح + الكود
```

---

## 4. المسارات / Routes

### 4.1 المسارات الرئيسية (web.php:123-136، داخل مجموعة `auth`)

| الطريقة | URI | Action | الاسم | Middleware |
|---|---|---|---|---|
| GET | `/signals` | `SignalController@index` | `signals.index` | `auth` فقط |
| GET | `/signals/create` | `@create` | `signals.create` | `auth` فقط |
| POST | `/signals` | `@store` | `signals.store` | `auth` فقط |
| GET | `/signals/export/excel` | `@exportExcel` | `signals.export.excel` | **بدون permission** |
| GET | `/signals/export/pdf` | `@exportPdf` | `signals.export.pdf` | **بدون permission** |
| GET | `/signals/{mainSignal}/edit` | `@edit` | `signals.edit` | `auth` فقط |
| PUT | `/signals/{mainSignal}` | `@update` | `signals.update` | `auth` فقط |
| DELETE | `/signals/{mainSignal}` | `@destroy` | `signals.destroy` | `auth` فقط |
| GET | `/signals/{mainSignal}` | `@show` | `signals.show` | **`permission:signals.view`** |

**ترتيب الـ route مهم:** التصدیر (`export/excel`, `export/pdf`) معرّف **قبل** `{mainSignal}/edit` حتى لا يفسّر Laravel `export` كمعرّف (تعليق في `web.php:127-128`). أيضًا `edit` قبل `show` لأن `{mainSignal}/edit` أكثر تحديدًا.

> `signals.show` هو **المسار الوحيد** في الموديول بـ `permission:` middleware صريح — الباقي يعتمد `auth` + `@can` في الواجهات فقط (§8).

### 4.2 مسارات الإعدادات (مجموعة `settings.` + `can:` لكل مجموعة)

**جهات الإشارة** (web.php:326-339):

| الطريقة | URI | Action | Middleware |
|---|---|---|---|
| GET | `settings/signal-authorities` | `index` | `can:signals.view` |
| GET / POST | `settings/signal-authorities/create` / `` | `create` / `store` | `can:signals.create` |
| GET / PUT | `settings/signal-authorities/{signalAuthority}/edit` / `` | `edit` / `update` | `can:signals.edit` |
| DELETE | `settings/signal-authorities/{signalAuthority}` | `destroy` | `can:signals.delete` |

**مجموعات جهات الإشارة** (web.php:342-355): نفس النمط تمامًا على `settings/signal-authority-groups` (`{signalAuthorityGroup}`).

**مضمون الإشارة** (web.php:374-387): نفس النمط على `settings/signal-contents` (`{signalContent}`).

> في الإعدادات، المجموعة `Route::middleware('can:signals.X')->group(...)` تغلّف كل المسارات — حماية مستوى route فعلية هنا (بخلاف المسارات الرئيسية).

---

## 5. منطق Controllers / Controller Logic

### 5.1 `SignalController` (app/Http/Controllers/SignalController.php — 315 سطر)

**دوال مساعدة:**

| الدالة | الأسطر | الوظيفة |
|---|---|---|
| `validationRules()` | 19–32 | قواعد التحقق المشتركة store/update (§6) |
| `authorityGroupsPayload()` | 38–49 | المجموعات بالشكل الذي يحتاجه JS: `{id, name, members[]}` — **أعضاء بأسمهم وليس IDs** لأن حقول الاختيار مخزّنة باسم الجهة |
| `filteredUnitsQuery(Request)` | 234–247 | استعلام التصدير: أول كرت لكل ثريد (MIN(ID) group by MAIN_SEND_ID)، join مع `MainSignalTBL` و`users` (لـ `receiver_name`)، فلاتر `date_from/to`، ترتيب تاريخ/وقت تنازلي. **ملاحظة:** لا يطبّق فلتر الوقت (`filter_by_time`) بخلاف `index()` |
| `saveUnits(array, MainSignal)` | 265–291 | لكل كارت: `SignalUnit::create`؛ ثم لكل حالة جهة غير فارغة: `SignalAuth::create` مع `CONTACT_NAME` = اسم الجهة و`SIGNAL_ID` = ID الكرت |
| `logSystemRecord(...)` | 293–305 | يكتب صف `SystemRecord` (مستخدم، جهاز، IP، عنوان، وصف، تاريخ، `ISACTIVE='1'`) |
| `buildNextSignalCode()` | 307–314 | `MAX(CAST(REPLACE(MainSignalCode,'SIGNAL-','') AS UNSIGNED))` + 1 → `SIGNAL-{n}` |

**الدوال العامة:**

| الدالة | الأسطر | الملخص |
|---|---|---|
| `index(Request)` | 55–94 | صف واحد لكل ثريد: أول كارت (`MIN(ID)` grouped by `MAIN_SEND_ID`) + `selectSub` يعدّ `replies_count` (كل كروت الثريد) + joins مع الثريد والمستخدم؛ فلاتر: `date_from/to`، ووقت اختياري عبر `filter_by_time` (`:80-87`)؛ ترتيب تنازلي تاريخ/وقت؛ paginate 20 `withQueryString` |
| `show(MainSignal)` | 99–113 | كل كروت الثريد بترتيب `ID` مع `sender` و`authStates` + تحميل `receiver` |
| `create()` | 115–123 | يمرر: `nextSignalCode`، `signalAuthorities` (مرتبة)، `signalAuthorityGroups` (payload)، `signalContents` (قائمة نصية) |
| `store(Request)` | 125–148 | validate → Transaction (إنشاء `MainSignal` + `saveUnits`) → `logSystemRecord('إضافة بيانات إشارة جديدة')` → redirect لصفحة الإنشاء برسالة نجاح تحوي الكود |
| `edit(MainSignal)` | 150–176 | يبني `$existingUnits` كمصفوفة للـ JS: `date`، `time` (قصّ 0,5)، `sender` (ID)، `content`، `subject`، `type`، `authorities` = `authStates->pluck('STATE','CONTACT_NAME')` |
| `update(Request, MainSignal)` | 178–204 | validate → Transaction: جمع IDs الكروت القديمة → حذف كل `SignalAuth` للثريد (`MAIN_SIGNAL_ID`+`CODE`) → حذف الكروت القديمة → `saveUnits` من جديد (**إعادة بناء كاملة، لا diff**) → سجل تدقيق → redirect |
| `destroy(MainSignal)` | 206–232 | Transaction: حذف `SignalAuth` ثم `SignalUnit` ثم `MainSignal`؛ ثم `SystemRecord::create` **مباشرة** (لا عبر helper — تناقض أسلوبي، العنوان `حذف اشارة`) |
| `exportExcel()` | 249–254 | `filteredUnitsQuery()->get()` → `SignalsExport` → `الإشارات.xlsx` |
| `exportPdf()` | 256–263 | `signals.export-pdf` عبر dompdf، A4 landscape → `الإشارات.pdf` — **بدون معالجة ArPHP** (خلافًا لتصديرات البلاغات — انظر §9) |

### 5.2 `SignalAuthorityGroupController` (69 سطر)

الوحيد بين إعدادات الموديول بمنطق إضافي:
- `index()`: eager load `authorities` + `withCount` + ترتيب `GROUP_NAME`.
- `create()`: كل الجهات + مصفوفة مختارة فارغة.
- `store()`: إنشاء + `sync($validated['authorities'])` على الـ pivot.
- `edit()`: المجموعة + كل الجهات + `selectedAuthorities` = IDs المحددة.
- `update()`: تحديث الاسم + `sync` (استبدال الاختيارات).
- `destroy()`: حذف — الـ pivot يتبع تلقائيًا عبر `onDelete cascade`.

### 5.3 `SignalAuthorityController` (56 سطر) / `SignalContentController` (56 سطر)

CRUD بسيط عبر FormRequests مع رسائل نجاح عربية وإعادة توجيه للفهرس. فرق صغير: `SignalAuthorityController@edit` يمرر `['authority' => $signalAuthority]` (و ليس compact).

---

## 6. قواعد التحقق من المدخلات / Validation

### 6.1 الموديول — `validationRules()` (SignalController.php:19-32)

الإدخال **متعدد الكروت**: مصفوفة `signals[]` يبنيها JS:

| القاعدة | الشرط |
|---|---|
| `signals` | required, array, **min:1** |
| `signals.*.date` | required, date |
| `signals.*.time` | required |
| `signals.*.sender` | required, `exists:SIGNAL_AUTHORITY,ID` |
| `signals.*.content` | required, string |
| `signals.*.subject` | nullable, string |
| `signals.*.type` | required, **`in:إشارة لاسلكية,رصد مرئي`** (سطرين صريحين) |
| `signals.*.authorities` | nullable, array (مفتاح = اسم الجهة، قيمة = `Correct`/`X`) |
| `signals.*.authorities.*` | nullable, **`in:Correct,X`** |

رسائل عربية مخصصة: `signals.*.sender.required` = "اختر جهة إرسال الإشارة"، `signals.*.content.required` = "اختر مضمون الإشارة" (`store()` `:127-130`، `update()` `:180-183`).

⚠️ **لا يوجد تحقق أن `CONTACT_NAME` المُرسل موجود فعلًا في `SIGNAL_AUTHORITY`** — أي نص يُحفظ كما هو (§12).

### 6.2 إعدادات الموديول (Form Requests — `authorize(): true` في الجميع)

| الملف | القواعد |
|---|---|
| `Store/UpdateSignalAuthorityRequest` | `SIGNAL_NAME`: required\|string\|**max:50** |
| `Store/UpdateSignalContentRequest` | `SIGNALCONTENT`: required\|string\|**max:500** |
| `StoreSignalAuthorityGroupRequest` | `GROUP_NAME`: required\|string\|max:100\|**unique** `SIGNAL_AUTHORITY_GROUP`؛ `authorities`: nullable\|array؛ `authorities.*`: integer\|`exists:SIGNAL_AUTHORITY,ID` — رسائل عربية مخصّصة |
| `UpdateSignalAuthorityGroupRequest` | كما Store مع `unique` **ignore** معرّف المجموعة: `->ignore($this->route('signalAuthorityGroup')->ID)` |

> الإعدادات المجموعة هي **الوحيدة في المشروع** (مع البلاغات جزئيًا) التي تستخدم `unique` — عكس `ReportingType` (§12 في وثيقة البلاغات).

---

## 7. الصلاحيات / Permissions

الصلاحيات المُعرّفة في `database/seeders/RolesAndPermissionsSeeder.php:29-32`:

| الصلاحية | الدور الممنوح | أين تُفحص |
|---|---|---|
| `signals.view` | `مشرف عام`، `موظف إشارات`، `مستخدم عادي` | middleware على `signals.show` + `can:` في الإعدادات + `@can` في القوائم |
| `signals.create` | `مشرف عام`، `موظف إشارات` | `can:` على إنشاء الإعدادات + `@can` (زر التسجيل) |
| `signals.edit` | `مشرف عام`، `موظف إشارات` | `can:` + `@can` (أزرار التعديل/الرد) |
| `signals.delete` | `مشرف عام` فقط | `can:` + `@can` (زر الحذف) |
| `signals.export_excel` | ⚠️ **غير موجودة في الـ seeder** | `@can` في `signals/index.blade.php:10` |
| `signals.export_pdf` | ⚠️ **غير موجودة في الـ seeder** | `@can` في `signals/index.blade.php:16` |

**التنقل** (`resources/views/partials/sidebar.blade.php`):
- قسم "الإشارات": `@canany(['signals.create','signals.view'])` (`:104`)، "تسجيل إشارة" بـ `@can('signals.create')` (`:123`)، "قائمة الإشارات" بـ `@can('signals.view')` (`:132`).
- الإعدادات: القسم `@canany(['reports.view','signals.view'])` (`:431`)، وروابط الإشارات الثلاثة بـ `@can('signals.view')` (`:465, 474, 492`).

**فجوة حرجة:** صلاحيتَي التصدير غير مُسجَّلتين → `@can('signals.export_excel')` و`@can('signals.export_pdf')` ترجعان `false` للجميع (إلا لو أُنشِئتا يدويًا في قاعدة البيانات) → **أزرار التصدير مخفية فعليًا لكل الأدوار**، مع أن المسارات نفسها `auth`-only تعمل لو طُلبت مباشرة. (§12)

---

## 8. التصدير / Exports

### 8.1 `app/Exports/SignalsExport.php` (64 سطر)

Maatwebsite Excel — `FromCollection, WithHeadings, WithMapping, WithStyles`، يأخذ `Collection $units` في المُنشئ:

- **7 أعمدة** (`:28-36`): كود الإشارة، مضمون الإشارة، نوع الإشارة، موضوع الإشارة، تاريخ الإشارة،توقيت الإشارة، متلقي الإشارة.
- `map()` (`:39-50`): `MainSignalCode`، `UNIT_SIGNAL_CONTENT`، `UNIT_SIGNAL_TYPE`، `UNIT_SIGNAL_SUBJECT`، `UNIT_SIGNAL_DATE`، `UNIT_SIGNAL_TIME`، `receiver_name` (من join مع `users`).
- تنسيق: عريض أبيض على `#001F3F` (`:52-63`).

التصدير يشمل **أول كرت لكل ثريد فقط** (وفلترة التاريخ) — الردود لا تُصدَّر.

### 8.2 PDF

`signals/export-pdf.blade.php` (43 سطر): RTL + DejaVu Sans + جدول بترويسة `#001F3F`، ورقة A4 landscape. **لا يوجد ArPHP glyph shaping** هنا (خلافًا لتصديرات البلاغات) — dompdf يعالج العربية بـ DejaVu مباشرة.

---

## 9. واجهات المستخدم / Views (Blade)

### 9.1 الشاشات الرئيسية (`resources/views/signals/`)

| الملف | الأسطر | الوظيفة |
|---|---|---|
| `index.blade.php` | 135 | قائمة الثريدات (صف لكل ثريد): أزرار تصدير (`:10` Excel عبر `@can('signals.export_excel')`، `:16` PDF) وزر إنشاء (`:22`)؛ فلاتر GET: `date_from` (افتراضي اليوم `:36`)، `date_to`، checkbox `filter_by_time` (`:46`) مع وقتين؛ الأعمدة: كود، مضمون، نوع (badge أزرق لاسلكية/بنفسجي مرئي)، موضوع، تاريخ/توقيت، متلقي، عدد الردود (يعرض `replies_count - 1` رد إن >1 وإلا "بدون ردود"، `:95-97`)، إجراءات (`@can` view/edit/delete `:105-117`) مع تأكيد حذف يذكر الردود |
| `create.blade.php` | 275 | إنشاء ثريد بـ **كروت ديناميكية**: عرض كود تلقائي، template `#signal-card-template`، JS يضيف كرتًا بأسماء حقول `signals[i][...]` + flatpickr عربي؛ مبدّل الجهات يتدرّج `''→Correct→X→''` عبر hidden inputs؛ المجموعات تطبّق الحالة على الأعضاء وتزامن حالة المجموعة؛ كرت واحد يُضاف عند التحميل |
| `edit.blade.php` | 284 | نفس واجهة الإنشاء مع تعبئة `@json($existingUnits)` لكل كرت موجود؛ زر "إضافة رد جديد"؛ يرسل PUT — **يستبدل كل الكروت** (§5.1) |
| `show.blade.php` | 70 | عرض الثريد كاملًا كمحادثة: الكود، اسم `receiver`، العدد؛ لكل كرت: badge "الإشارة الأساسية" أو "رد #n"، شارة النوع، جهة الإرسال، المضمون، الموضوع، وحالات الجهات كـ pills (Correct أخضر ✓ / X أحمر ✗)؛ زر تعديل/رد `@can('signals.edit')` (`:66`) |
| `export-pdf.blade.php` | 43 | قالب dompdf (§8.2) |

**أصول مرافقة (غير مستخدمة حاليًا بالكامل):** `resources/js/signals-list.js` (بيانات mock قديمة)، `resources/js/add-signal.js` (يحتوي redirect قديم لـ `signals-list.html`)، `resources/css/{signals-list,add-signal}.css` — الواجهات الحالية تستخدم Tailwind + JS مضمّن في Blade.

### 9.2 شاشات الإعدادات (`resources/views/settings/`)

| المجموعة | الملفات | ملاحظات |
|---|---|---|
| `signal-authorities/` | `index` (71)، `create` (47)، `edit` (47) | حقل واحد `SIGNAL_NAME`؛ أزرار محميّة `@can('signals.edit'/'delete')` |
| `signal-authority-groups/` | `index` (92)، `create` (42)، `edit` (47)، **`_fields`** (48) | partial مشترك `_fields` يستقبل `formId` + `groupName` اختياري؛ صندوق بحث يفلتر مفاتيح الأعضاء؛ checkboxes باسم `authorities[]` بقيم IDs؛ حالة فارغة لو لا توجد جهات |
| `signal-contents/` | `index` (71)، `create` (47)، `edit` (47) | حقل واحد `SIGNALCONTENT` |

---

## 10. سجل التدقيق / Audit

كل عملية تكتب صفًا في `SystemRecord` (مستخدم، جهاز ≤255، IP، عنوان، وصف، تاريخ، `ISACTIVE='1'`، `USER_ID`):

| العملية | الآلية | الموقع |
|---|---|---|
| إنشاء | `logSystemRecord('إضافة بيانات إشارة جديدة')` | `SignalController.php:143` |
| تعديل | `logSystemRecord('تعديل بيانات إشارة')` | `:199` |
| حذف | `SystemRecord::create` **مباشرة** بعنوان `حذف اشارة` | `:220-229` |

⚠️ الحذف يخالف الأسلوب (لا يستخدم الـ helper) — انظر §12.

---

## 11. المعالم المعروفة / Known Issues & Tech Debt

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 1 | **صلاحيتا التصدير غير مُعرَّفتين في الـ seeder** بينما تفحصهما الواجهة | `RolesAndPermissionsSeeder.php` (غائبتان) مقابل `signals/index.blade.php:10,16` | أزرار Excel/PDF مخفية للجميع؛ المسار يعمل مباشرة لمن يعرفه (حماية `auth` فقط) — عدم اتساق بين الواجهة والمسار |
| 2 | **migration يحذف PK من جدول باسم خاطئ**: `unit_signal` (صغير) بدل `SIGNAL_UNIT` | `database/migrations/2026_09_28_122211_allow_duplicate_id_in_unit_signal.php:16-24` | على MySQL حساس لحالة الأسماء (Linux/MySQL default) يفشل أو يستهدف جدولًا خاطئًا؛ على Windows يعمل بسبب عدم الحساسية — يشكّل فرق سلوك بين البيئات |
| 3 | **`ID` بلا auto-increment ولا PK** بعد migration §2.2(3) رغم أن `SignalUnit` يعلن `$incrementing = true` | `SIGNAL_UNIT` + `app/Models/SignalUnit.php:11` | `SignalUnit::create` يعتمد على PK غير موجود/auto — قد يفشل أو يتصرف بشكل غير متوقع حسب حالة القاعدة الفعلية |
| 4 | **`update()` يعيد بناء الثريد كاملًا** (حذف + إعادة إنشاء) | `SignalController.php:178-204` | فقدان أي أثر للتعديلات الجزئية؛ أي كرت مضاف "دوسيًا" يُمحى؛ لا يوجد diff |
| 5 | **لا تحقق من وجود اسم الجهة** المُرسل في `SignalAuth.CONTACT_NAME` | `saveUnits()` `:279-289` | حفظ أي نص (أسماء يتيمة بعد حذف جهة، أو حقن نص) — عكس `signals.*.sender` المقيّد بـ `exists` |
| 6 | **مسارات التصدير بلا `permission:` middleware** — و`index`/`store`/`update`/`destroy` كذلك | `routes/web.php:123-134` | حماية المسارات الرئيسية `auth` فقط؛ من يملك رابطًا يستطيع التنفيذ ما لم تكن الواجهة تحميه بـ `@can` فقط |
| 7 | **ثغرة سباق في كود الثريد**: `MAX` + 1 بلا قفل ولا unique | `buildNextSignalCode()` `:307-314` | تسيران متزامنان قد تحصلان على `SIGNAL-n` نفسه |
| 8 | **`filteredUnitsQuery` تجاهل فلتر الوقت** بينما `index()` تطبقه | `:234-247` مقابل `:80-87` | التصدير لا يحترم `filter_by_time` — نتائج أوسع مما تظهر في الشاشة |
| 9 | **`destroy` يخالف أسلوب التدقيق**: `SystemRecord::create` مباشر + عنوان `حذف اشارة` (تهجئة/أسلوب مختلف) بدل `logSystemRecord()` | `:220-229` مقابل `:293-305` | تناقض في سجل المراجعة (صعب التصفية) |
| 10 | **نماذج/جداول legacy مستعملة**: `RecieveSignal` مستورد في `DashboardController.php:6` ولا يُستخدم؛ `SendSignal` و`RecSignalAuth` بلا استدعاءات | `app/Models/{RecieveSignal,SendSignal,RecSignalAuth}.php` | حشو وارتباك للمطور الجديد — وثّق §2.3 قبل أي حذف |
| 11 | **أصول JS/CSS ميتة**: بيانات mock وredirect لـ `signals-list.html` قديم | `resources/js/{signals-list,add-signal}.js` | ملفات لا تُحمَّل من أي Blade — مرشحة للحذف |
| 12 | **`SignalAuth.SIGNAL_ID` بمعنيين** (وحدة قديمة مقابل ثريد قديم) | `app/Models/SignalAuth.php:24` مقابل `RecSignalAuth` | أي استعلام جديد يجب أن يقيّد نفسه بالمسار النشط |

---

## 12. مسرد المصطلحات / Glossary

| في الكود | المعنى |
|---|---|
| `MainSignal` / `MainSignalTBL` | جذر الثريد (المحادثة كاملة) |
| `MainSignalCode` | كود الثريد `SIGNAL-{n}` |
| `RECEIVER_ID` | المستخدم الذي أنشأ/استلم الثريد |
| `SignalUnit` / `SIGNAL_UNIT` | كرت داخل الثريد (إشارة أساسية أو رد) |
| `MAIN_SEND_ID` + `MAIN_SEND_CODE` | المفتاح المزدوج للثريد على مستوى الكرت |
| `UNIT_SIGNAL_TYPE` | نوع الإشارة: `إشارة لاسلكية` أو `رصد مرئي` |
| `SignalAuth` / `SIGNAL_AUTH` | حالة جهة واحدة على كرت واحد |
| `CONTACT_NAME` | اسم الجهة نصًا (ليس FK) |
| `STATE` | `Correct` (✓) أو `X` (✗) |
| `SIGNAL_AUTHORITY.SIGNAL_NAME` | اسم جهة الإشارة (مرسل/مستقبل) |
| `SIGNAL_AUTHORITY_GROUP` | مجموعة جهات — تُطبق حالتها دفعة واحدة |
| `SIGNALCONTENT` | بند مضمون إشارة مسبق |
| `replies_count` | عدد كروت الثريد (يُعرض كـ `replies_count - 1` "ردود") |

---

## 13. مقارنة سريعة: البلاغات مقابل الإشارات / Quick Contrast

| البُعد | البلاغات | الإشارات |
|---|---|---|
| الوثيقة | [reports-module.md](reports-module.md) | هذه الوثيقة |
| الجذر | `RECIEVE_REPORT` (بلاغ واحد) | `MainSignalTBL` (ثريد) |
| الوحدات الفرعية | إصابات، ووفيات، مرفقات | كروت (units) + حالات جهات |
| الحالة/القفل | 4 حالات + قفل إداري | لا يوجد — `Correct/X` فقط |
| الترقيم | `REP-{n}` | `SIGNAL-{n}` |
| جهة البلاغ/الإشارة | `REPORTING_TYPES.AUTHORITY` | `SIGNAL_AUTHORITY` (جدول منفصل) |
| جهات الإخطار/الحالة | `REPORT_AUTHS` (أسماء) | `SIGNAL_AUTH` (اسم + حالة) |
| التحديث | in-place للحقول + إعادة بناء الجداول التابعة | إعادة بناء الثريد كاملًا |
| الحماية الرئيسية | `auth` + `@can` | `auth` + `@can` (+ `permission:signals.view` على `show` فقط) |
| تصدير PDF | dompdf + **ArPHP glyph shaping** | dompdf + DejaVu فقط |
