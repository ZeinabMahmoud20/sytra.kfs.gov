# وحدة دليل الاتصال — Contact Guides Module

> توثيق تقني ثنائي اللغة (عربي + مصطلحات إنجليزية) موجّه للمطورين.
> آخر مراجعة ضد الكود: أكتوبر 2026 — اللوثج الرئيسي: `app/Http/Controllers/ContactGuideController.php` (118 سطر).
> الوثائق الشريكة: [البلاغات](reports-module.md) · [الإشارات](signals-module.md) · [التكليفات](tasks-module.md).

---

## 1. نظرة عامة / Overview

وحدة **دليل الاتصال** فهرس جهات الاتصال: كل سجل يمثّل **جهة/إدارة** (اسم الجهة، اسم المسؤول، 3 أرقام تليفون). الوحدة الوحيدة في المشروع التي تدعم **استيراد دفعة من Excel** (Upsert).

| الخاصية | القيمة |
|---|---|
| السجلات | سجل واحد لكل **جهة/إدارة** (مفتاح التفرّد = `department_name`) |
| العمليات | CRUD كامل + استيراد Excel مع upsert |
| الفلاتر | بحث نصي (اسم الجهة/المسؤول) + فلتر جهة من قائمة القيم المميزة |
| الترقيم | 20/صفحة |
| التصدير | ❌ لا يوجد |
| الحماية | **كل مسار محميّ بـ `can:contact-guides.*`** (middleware حقيقي — من أنظف الموديولات في المشروع) |

**مسار الكود الرئيسي:**

```
routes/web.php (184-205)                      → 5 مجموعات محميّة بـ can:
app/Http/Controllers/ContactGuideController.php  → CRUD + الاستيراد (118)
app/Imports/ContactGuidesImport.php            → Maatwebsite Excel upsert (62)
app/Models/ContactGuide.php                    → 5 حقول fillable فقط (16)
app/Http/Requests/{Store,Update,Import}ContactGuideRequest.php
resources/views/contact-guides/{index,create,edit,import}.blade.php
```

---

## 2. مخطط قاعدة البيانات / Database Schema

### `contact_guides`

Migration: `2026_09_01_000001_create_contact_guides_table.php` — Model: `app/Models/ContactGuide.php` (الافتراضيات: `contact_guides` / PK `id` / timestamps)

| العمود | النوع الأصلي | ملاحظات |
|---|---|---|
| `id` | bigIncrements | PK |
| `department_name` | string **unique** ← تغيّر إلى `text` | **اسم الجهة — مفتاح التفرّد** (سطر 13 في migration) |
| `manager_name` | string nullable ← `text` nullable | اسم المسؤول |
| `phone_number` | string nullable | تليفون الجهة |
| `landline_number` | string nullable | تليفون أرضي |
| `additional_phone` | string nullable | رقم إضافي |
| `created_at` / `updated_at` | timestamp | Laravel timestamps |

**لا توجد FKs** — الجدول مستقل تمامًا (لا علاقة بالجهات `task_entities` ولا `City`/`Village`).

### 2.1 Alter migration مهم

`database/migrations/2026_09_14_114232_alter_contact_guides_change_column_types.php`:

```php
$table->text('department_name')->change();          // :15
$table->text('manager_name')->nullable()->change(); // :16
```

> ⚠️ **خطر محتمل — Unique + TEXT:** في MySQL لا يُسمح بفهرس على عمود `TEXT`/`BLOB` بدون طول بادئة (`BLOB/TEXT column used in key specification without a key length`). تحويل عمود `VARCHAR UNIQUE` إلى `TEXT` عبر `change()` إما:
> - **يفشل الـ migration** (لو الـ index موجود)، أو
> - **يُسقط الـ index** تلقائيًا (سلوك بعض الإصدارات/الأpaces).
>
> **يلزم التحقق من القاعدة الفعلية:**
> ```sql
> SHOW COLUMNS FROM contact_guides;
> SHOW INDEX FROM contact_guides;   -- هل ما زال unique موجودًا؟
> ```
> **الأثر:** قاعدة `unique:contact_guides,department_name` في الـ FormRequests تبقى فعّالة **على مستوى التطبيق** (استعلام SELECT) حتى لو سقط الـ index — لكن الحماسة على مستوى القاعدة (Race condition) تكون مفقودة. (§11)

---

## 3. المسارات / Routes

**كل المسارات محميّة بـ `can:` على مستوى الـ route** (web.php:184-205) — كل مجموعة بصلاحية مختلفة:

| الطريقة | URI | Action | الاسم | Middleware |
|---|---|---|---|---|
| GET | `contact-guides` | `ContactGuideController@index` | `contact-guides.index` | `can:contact-guides.view` |
| GET | `contact-guides/create` | `@create` | `contact-guides.create` | `can:contact-guides.create` |
| POST | `contact-guides` | `@store` | `contact-guides.store` | `can:contact-guides.create` |
| GET | `contact-guides/{contactGuide}/edit` | `@edit` | `contact-guides.edit` | `can:contact-guides.edit` |
| PUT | `contact-guides/{contactGuide}` | `@update` | `contact-guides.update` | `can:contact-guides.edit` |
| DELETE | `contact-guides/{contactGuide}` | `@destroy` | `contact-guides.destroy` | `can:contact-guides.delete` |
| GET | `contact-guides-import` | `@showImportForm` | `contact-guides.import.form` | `can:contact-guides.import` |
| POST | `contact-guides-import` | `@import` | `contact-guides.import` | `can:contact-guides.import` |

**ملاحظات:**
- مسارات الاستيراد **ليست** متداخلة تحت `contact-guides/` — بل بادئة منفصلة `contact-guides-import` (لأنها ليست part من resource CRUD).
- Route model binding ضمني `{contactGuide}` → `App\Models\ContactGuide` (تسمية camelCase المتعارف عليها في Laravel).

> ✅ هذه الوحدة تتفوّق على وحدات البلاغات/الإشارات/التكليفات: الحماية هنا على مستوى الـ route بالكامل (مجموعات `can:` لكل صلاحية)، بينما في تلك الوحدات المسارات الأساسية `auth` فقط.

---

## 4. منطق Controller / Controller Logic

`app/Http/Controllers/ContactGuideController.php` (118 سطر) — كل الدوال عامة (public) ومباشرة:

| الدالة | الأسطر | الملخص |
|---|---|---|
| `index(Request)` | 17–42 | بناء query: فلتر `search` → LIKE على `department_name` **أو** `manager_name` داخل closure (`:21-27`)؛ فلتر `department` → مطابقة تامة (`:29-31`؛ ملاحظة: **بعد** closure البحث، فالبحث + الفلتر يعملان معًا)؛ ترتيب `department_name`؛ `paginate(20)->withQueryString()` (`:33`)؛ قائمة `$departments` من `distinct()` مرتبة (`:35-39`) → `contact-guides.index` |
| `create()` | 44–47 | `view('contact-guides.create')` |
| `store(StoreContactGuideRequest)` | 49–56 | `ContactGuide::create($request->validated())` → flash "تم إضافة سجل دليل الاتصال بنجاح" |
| `edit(ContactGuide)` | 58–61 | `view('contact-guides.edit', compact('contactGuide'))` |
| `update(UpdateContactGuideRequest, ContactGuide)` | 63–70 | `$contactGuide->update($request->validated())` → flash "تم تعديل السجل بنجاح" |
| `destroy(ContactGuide)` | 72–79 | `$contactGuide->delete()` → flash "تم حذف السجل بنجاح" |
| `showImportForm()` | 81–84 | `view('contact-guides.import')` |
| `import(ImportContactGuidesRequest)` | 86–117 | `Excel::import($import, $request->file('file'))` (`:90`)؛ فحص `$import->failures()`؛ لو في أخطاء: بناء تفاصيل (أول 20 فقط) + `Log::warning` + flash تحذير + `with('failureDetails', $details)` (`:94-112`)؛ وإلا flash نجاح "أُضيفت الإدارات الجديدة وتُحدّثت بيانات الإدارات الموجودة" (`:116`) |

**لا يوجد:** Policy، Gates، روابط AJAX، تصدير، ولا سجل تدقيق (`SystemRecord`) — الوحدة الوحيدة بين الموديولات الأربعة التي لا تكتب سجل مراجعة.

---

## 5. قواعد التحقق / Validation

### 5.1 `StoreContactGuideRequest`

| الحقل | القاعدة |
|---|---|
| `department_name` | required, string, **`unique:contact_guides,department_name`** |
| `manager_name` | nullable, string |
| `phone_number` | nullable, string, **max:50** |
| `landline_number` | nullable, string, max:50 |
| `additional_phone` | nullable, string, max:50 |

### 5.2 `UpdateContactGuideRequest`

نفس القواعد، مع **`unique` يتجاهل السجل الحالي**:
```php
'unique:contact_guides,department_name,' . $this->route('contactGuide')->id
```

### 5.3 `ImportContactGuidesRequest`

| الحقل | القاعدة |
|---|---|
| `file` | required, file, **`mimes:xlsx,xls,csv`**, **max:5120** (5MB) |

> `authorize()` في الثلاث Requests يعيد `true` — التفويض بالكامل على الـ middleware (وهو أمر سليم هنا، بخلاف موديول التكليفات).

---

## 6. تدفق الاستيراد / Import Flow

`app/Imports/ContactGuidesImport.php` (62 سطر) — Maatwebsite Excel:

### 6.1 الـ Concerns المطبَّقة

```php
implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithUpserts
use SkipsFailures;
```

### 6.2 التمييز بـ Upsert

```php
public function uniqueBy(): array { return ['department_name']; }  // :19-22
```
→ الصف الموجود مسبقًا بنفس `department_name` **يُحدَّث** بدل الإدراج.

### 6.3 منطق الصفوف

```php
public function model(array $row): ?ContactGuide   // :24-39
```
1. `trim` على `department_name` (`:26`).
2. **تحقق من التكرار داخل الملف** عبر `$seenNames` (`:28-30`, `:41-50`): إذا كان الاسم فارغًا أو مكررًا → `return null` (يُتخطى الصف بصمت).
3. وإلا: `new ContactGuide([...])` مع تحويل الأرقام إلى `(string)` (`:32-38`).

### 6.4 قواعد التحقق داخل الاستيراد (`:52-61`)

```php
'department_name' => ['required','string'],
'manager_name'    => ['nullable','string'],
'phone_number'    => ['nullable'],   // ⚠️ بلا string|max:50
'landline_number' => ['nullable'],
'additional_phone'=> ['nullable'],
```

> ⚠️ **الفارق عن الـ FormRequests:** الاستيراد لا يفرض `max:50` على الأرقام (urvailable في الـ UI العادي) — بيانات أطول تمر دون تحقق.

### 6.5 سلوك الصفوف الفاشلة

- `SkipsOnFailure` + `SkipsFailures` → الصف الفاشل **لا يوقف** الاستيراد ويُجمَّع.
- `ContactGuideController@import` (`:94-112`): يعرض حتى **20** تفصيلًا أول في الجلسة (`failureDetails`) ويكتب العدد الكامل في `Log::warning`.
- ⚠️ **التفاصيل تُخزَّن في الـ session** — قد تكبر مع عدد الأخطاء (Though capped at 20 entries + summary).

### 6.6 صيغة ملف Excel المطلوبة

| العمود | مطلوب | ملاحظات |
|---|---|---|
| `department_name` | ✅ | اسم الجهة — مفتاح التفرّد |
| `manager_name` | — | اسم المسؤول |
| `phone_number` | — | تليفون الجهة |
| `landline_number` | — | تليفون أرضي |
| `additional_phone` | — | رقم إضافي |

الصيغ المقبولة: `.xlsx`, `.xls`, `.csv` — بحد أقصى 5MB.

---

## 7. الصلاحيات / Permissions

الصلاحيات الخمس مُنشأة في `database/seeders/RolesAndPermissionsSeeder.php:47-51`:

| الصلاحية | وظيفتها |
|---|---|
| `contact-guides.view` | عرض القائمة + الفلاتر |
| `contact-guides.create` | إضافة سجل + شاشة الإنشاء |
| `contact-guides.edit` | تعديل + شاشة التعديل |
| `contact-guides.delete` | الحذف |
| `contact-guides.import` | **شاشة الاستيراد + تنفيذ الاستيراد** |

**الأدوار:** مثل بقية موديولات المشروع، **دور `مشرف عام` فقط** يحصل عليها (عبر `Permission::all()` — `RolesAndPermissionsSeeder.php:67-68`). الأدوار الأخرى (`موظف استقبال بلاغات`، `موظف إشارات`، `موظف تمام`، `مستخدم عادي`) **لا تملك أي صلاحية contact-guides** — أي أن الوحدة عمليًا **للمشرف العام فقط** حاليًا.

**أين تُفحص:**

| الطبقة | الموقع |
|---|---|
| Route middleware | `routes/web.php:184, 188, 193, 198, 202` |
| Blade `@can` | `contact-guides/index.blade.php` — زر الاستيراد، زر الإضافة، زر التعديل، نموذج الحذف |
| Sidebar | `partials/sidebar.blade.php:216` — `@canany(['contact-guides.create','contact-guides.view','contact-guides.import'])`؛ فرعي: `:235` create، `:244` view، `:253` import |

---

## 8. واجهات المستخدم / Views (Blade)

`resources/views/contact-guides/` — 4 ملفات فقط:

| الملف | الأسطر | الوظيفة |
|---|---|---|
| `index.blade.php` | 128 | القائمة: أزرار "استيراد Excel" (`@can('contact-guides.import')`) و"إضافة جهة اتصال" (`@can('contact-guides.create')`)، عرض flash success/warning + **قائمة `failureDetails` قابلة للطي** (max-h-48 overflow)، نموذج فلاتر GET (بحث + select الجهة من `$departments` + زر فلتر + زر إعادة تعيين)، الجدول: `#`، اسم الجهة، اسم المسئول، تليفون الجهة، تليفون أرضى، رقم إضافى، الإجراءات (`@can('contact-guides.edit')` / `@can('contact-guides.delete')` + confirm JS)، حالة فارغة، pagination |
| `create.blade.php` | 66 | النموذج: رابط رجوع، CSRF، عرض أخطاء التحقق، `department_name` (required)، `manager_name`، حقول التليفون الثلاثة بـ `dir="ltr"`، زر الحفظ |
| `edit.blade.php` | 62 | نفس التخطيط بـ PUT + تعبئة القيم من `old()` أو الموديل |
| `import.blade.php` | 53 | `enctype="multipart/form-data"`، تعليمات العناوين المطلوبة/الاختيارية، شرح سلوك الـ upsert والتكرار داخل الملف، file input (`accept=".xlsx,.xls,.csv"`)، زر الاستيراد |

> ⏳ التوثيق الإنجليزي: `directory structure` — الملفات **`index/create/edit/import`** فقط؛ **لا توجد شاشة `show`** (السجل يُعرض مباشرة في القائمة).

---

## 9. الموديل / Model

`app/Models/ContactGuide.php` (16 سطر) — أبسط موديل في المشروع:

```php
class ContactGuide extends Model
{
    protected $fillable = [
        'department_name', 'manager_name',
        'phone_number', 'landline_number', 'additional_phone',
    ];
}
```

- لا `$table` (الافتراضي `contact_guides`)، لا `$primaryKey` (افتراضي `id`)، لا `$casts`، لا علاقات، لا scopes.
- `$timestamps = true` (افتراضي) — الجدول له `created_at`/`updated_at`.
- **لا يوجد `HasFactory`** → لا يمكن استخدام `ContactGuide::factory()`.

---

## 10. ملخص التصميم / Design Summary

```
┌─────────────────────────────────────────────────────────┐
│  Spatie: contact-guides.view/create/edit/delete/import  │
│  → middleware can:* على كل مسار (حماية كاملة)          │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│  ContactGuideController (CRUD + import)                 │
│  index: search LIKE + department exact + distinct list  │
└───────────────────────────┬─────────────────────────────┘
                            ▼
┌─────────────────────────────────────────────────────────┐
│  contact_guides (department_name = مفتاح التفرّد)        │
│  + timestamps                                            │
└─────────────────────────────────────────────────────────┘

الاستيراد:  Excel → ContactGuidesImport
              ├─ uniqueBy: ['department_name']  → Upsert
              ├─ $seenNames → تخطي التكرار داخل الملف
              └─ SkipsOnFailure → تقرير أخطاء (20 أولًا)
```

---

## 11. المعالم المعروفة / Known Issues & Tech Debt

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 1 | **`department_name` تحوّل إلى `TEXT` مع بقاء/سقوط فهرس UNIQUE** — MySQL يرفض فهرسًا على TEXT بدون طول بادئة | `2026_09_14_114232_alter_contact_guides_change_column_types.php:15` | **يحتاج تحقق**: `SHOW INDEX FROM contact_guides`. إن سقط الـ index → Race condition عند إدخال متزامن لنفس الجهة (التطبيقي يظل يمنع التكرار لكن بلا ضمانة على مستوى القاعدة) |
| 2 | **الاستيراد لا يفرض `max:50` على الأرقام** بينما الـ FormRequests تفرضه | `ContactGuidesImport::rules():57-59` | بيانات أطول من 50 حرفًا تمر عبر الاستيراد دون تحقق — تختلف عن الإدخال اليدوي |
| 3 | **استيراد ملف فارغ/بلا صفوف صالحة يُبلّغ كنجاح** | `ContactGuideController@import:114-116` | لا يوجد تحقق "هل أُدرج شيء فعلًا؟" — رسالة النجاح تُعرض دائمًا عند غياب الأخطاء |
| 4 | **تفاصيل الأخطاء تُخزَّن في الـ session** | `:111` | session payload كبير مع ملفات بها أخطاء كثيرة (محدود بـ 20 مدخل + ملخص) |
| 5 | **لا سجل تدقيق (`SystemRecord`)** لعمليات الإضافة/التعديل/الحذف | الـ controller كامل | الوحدة الوحيدة بلا أثر مراجعة — لا يمكن معرفة من عدّل دليل الاتصال ومتى |
| 6 | **لا شاشة `show`** ولا تصدير | — | لا يمكن تصدير دليل الاتصال كـ PDF/Excel لمشاركته مع جهات أخرى |
| 7 | **لا Policy ولا `authorize()` فعّال** | Requests → `true` | مقبول هنا لأن الـ middleware يكفي، لكن أي مسار جديد بلا `can:` سيكون مكشوفًا بالكامل |
| 8 | **الصلاحيات ممنوحة لـ `مشرف عام` فقط** | `RolesAndPermissionsSeeder.php:67-68` | الوحدة غير متاحة لغير المشرف العام — إن كان المطلوب توسيعها فتعديل الـ seeder |
| 9 | **البحث `LIKE` ب wildcards على عمود TEXT** بلا full-text index | `index():24-25` | غير مقبول عند نمو البيانات؛ يلزم فهرس أو بحث full-text |
| 10 | **لا `HasFactory` / لا اختبارات** | `ContactGuide` | لا يمكن كتابة اختبارات feature بدون Factory يدوي |
| 11 | **`manager_name` في الـ import لا يُقصّ (trim)** بخلاف `department_name` | `ContactGuidesImport::model():34` | أسماء بمسافات زائدة تُخزَّن وتظهر في القائمة |

---

## 12. مسرد المصطلحات / Glossary

| في الكود | المعنى |
|---|---|
| `ContactGuide` / `contact_guides` | سجل جهة اتصال |
| `department_name` | **اسم الجهة/الإدارة — مفتاح التفرّد الفريد** (ليس اسم قسم) |
| `manager_name` | اسم المسؤول عن الجهة |
| `phone_number` | تليفون الجهة |
| `landline_number` | تليفون أرضي |
| `additional_phone` | رقم إضافي |
| `uniqueBy()` | عمود الـ upsert في الاستيراد = `department_name` |
| `$seenNames` | تتبّع التكرار داخل ملف Excel الواحد |
| `failureDetails` | مفتاح session يحمل تفاصيل أخطاء الاستيراد |
| `SkipsOnFailure` | سلوك الصف الفاشل: يُتخطى ولا يوقف الدفعة |

---

## 13. مقارنة سريعة / Cross-Module Contrast

| البُعد | البلاغات | الإشارات | التكليفات | دليل الاتصال |
|---|---|---|---|---|
| الوثيقة | [reports](reports-module.md) | [signals](signals-module.md) | [tasks](tasks-module.md) | هذه الوثيقة |
| نوع الكيان | بلاغ | ثريد + كروت | تكليف | سجل جهة اتصال |
| حماية المسارات | `auth` فقط | `auth` فقط (+ `permission` على show) | `auth` فقط | **`can:` على كل مسار** ✅ |
| Policy | ❌ | ❌ | ✅ (لكن ناقصة التطبيق) | ❌ (لا حاجة) |
| استيراد Excel | ❌ | ❌ | ❌ | **✅ Upsert** |
| تصدير | ✅ Excel+PDF | ✅ Excel+PDF | ❌ | ❌ |
| سجل تدقيق | ✅ SystemRecord | ✅ SystemRecord | ✅ task_status_logs | ❌ |
| إشعارات | ❌ | ❌ | ✅ database | ❌ |
