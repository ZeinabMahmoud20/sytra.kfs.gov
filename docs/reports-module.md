# وحدة البلاغات — Reports Module

> توثيق تقني ثنائي اللغة (عربي + مصطلحات إنجليزية) موجّه للمطورين.
> آخر مراجعة ضد الكود: أكتوبر 2026 — Laravel 12-style app (bootstrap/app.php) + Spatie Permission.

---

## 1. نظرة عامة / Overview

وحدة البلاغات هي الموديول الأساسي في النظام: استقبال بلاغات المواطنين عن الحوادث، تتبع دورة حياتها حتى الإقفال، وإصدار إحصائيات وتقارير (قوائم، لوحات تحكم، إحصائيات شهرية، تصدير Excel/PDF).

**مسار الكود الرئيسي:**

```
routes/web.php                          → تعريف كل مسارات الموديول
app/Http/Controllers/
  ReportController.php                  → CRUD الأساسي + المرفقات + التصدير (882 سطر)
  ReportDashboardController.php         → لوحة التحكم الإحصائية
  MonthlyReportStatsController.php      → الإحصائيات الشهرية
  ReportingTypeController.php           → إعدادات أنواع البلاغات وجهات البلاغ
  NotifiedAuthController.php            → إعدادات جهات الإخطار
  AttachmentController.php              → خدمة/تحميل الملفات المرفقة
app/Models/RecieveReport.php            → النموذج الرئيسي (جدول RECIEVE_REPORT)
app/Exports/ReportsExport.php           → تصدير القائمة Excel
database/migrations/2026_01_01_*.php    → مخطط الجداول
resources/views/reports/*               → كل شاشات الواجهة
```

**الوحدات المرتبطة:** `City`/`Village` (المركز/القرية)، `User` (متلقي البلاغ)، ووحدة `Signals` (الإشارات) — لكن الإشارات تستخدم جداول ومصطلحات منفصلة (انظر §2.7).

---

## 2. مخطط قاعدة البيانات / Database Schema

### 2.1 `RECIEVE_REPORT` — جدول البلاغات (الجدول الرئيسي)

Migration: `database/migrations/2026_01_01_000012_create_recieve_report_table.php`
Model: `app/Models/RecieveReport.php` — PK: `ID`، بلا `$timestamps`، يُحذَّف/يُعدَّل من الاستيراد القديم.

| العمود / Column | النوع | ملاحظات |
|---|---|---|
| `ID` | bigIncrements | PK |
| `REPORTER_SSN` | string(50), nullable | الرقم القومي للمبلّغ — التحقق: 14 رقمًا يبدأ بـ 2 أو 3 |
| `REPORTER_NAME` | string(50), nullable | اسم المبلّغ — max 50 |
| `REPORTING_Auth` | string(500), nullable | جهة البلاغ (نص) — fallback فقط، المرجع هو `REPORTING_TYPES.AUTHORITY` |
| `REPORTING_SORT` | unsignedBigInteger, nullable | نوع البلاغ → FK منطقي إلى `REPORTING_TYPES.REPORT_ID` (بدون DB constraint) |
| `REPORT_START_DATE` | date, nullable | تاريخ تقديم البلاغ |
| `REPORT_START_TIME` | time, nullable | وقت تقديم البلاغ |
| `REPORT_RECIPIENT` | unsignedBigInteger, nullable | المستخدم الذي سجّل البلاغ → `users.id` |
| `CITY` | unsignedBigInteger, nullable | المركز/المدينة → `CITY.CITY_ID` |
| `VILLAGE` | unsignedBigInteger, nullable | القرية → `VILLAGE.VILLAGE_ID` |
| `X_AXIS` / `Y_AXIS` | unsignedBigInteger, nullable | الإحداثيات |
| `DAMAGE` | text, nullable | وصف الأضرار — **مطلوب** في الفورم |
| `PLACE_Accident` | text, nullable | مكان الحادث — max 500 حرف |
| `Deceased_Num` | integer, nullable | عدد الوفيات (أكبر من أو يساوي عدد صفوف `DEATHS`) |
| `INFECTED_NUM` | integer, nullable | عدد المصابين (أكبر من أو يساوي عدد صفوف `INJURED`) |
| `REPORT_END_DATE` / `REPORT_END_TIME` | date / time, nullable | تُملأ عند الإقفال (أول مرة تُختار فيها `تم الانتهاء`) |
| `REQUEST_STATUS` | string(50), nullable | الحالة — من `ReportController::$statuses` (§4) |
| `IS_LOCKED` | boolean, default 0 | **غير مستخدم فعليًا في المنطق** — الكود يعتمد على `REQUEST_STATUS` (§12) |
| `LOCKED_BY` | unsignedBigInteger, nullable | أضافته migration: `2026_08_11_144019_add_locked_by_to_recieve_report_table.php` مع FK → `users.id` (nullOnDelete) |
| `REPORT_REGISTER_NUMBER` | string(500), nullable | رقم قيد البلاغ بصيغة `REP-{n}` — يُحسب بـ `buildNextRegisterNumber()` |
| `REPORT_FOLLOWUP_NUMBER` | text, nullable | رقم التليفون (موبايل أو أرضي) |
| `NOTIFIED_AUTHORITIES` | text, nullable | **"الإجراءات المتخذة"** نص حر (max 5000) — يُملأ في التعديل فقط، يُجبر على null عند الإنشاء |

**تغييرات لاحقة (alter migrations):**
- `2026_09_28_110934_modify_recieve_report_table_for_import.php`: `LOCKED_BY` nullable افتراضيًا، `IS_LOCKED` افتراضه 0.
- `2026_09_29_000000_trim_reporting_authorities.php`: ترحيل بيانات يقصّ مسافات `REPORTING_TYPES.AUTHORITY` و`RECIEVE_REPORT.REPORTING_Auth` (لا رجوع فيه).

### 2.2 `REPORTING_TYPES` — أنواع البلاغات وجهات البلاغ

Migration: `2026_01_01_000005_create_reporting_types_table.php` — Model: `app/Models/ReportingType.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `REPORT_ID` | bigIncrements | PK |
| `REPORT_SORT` | string(500) | اسم نوع البلاغ (يُخزَّن كقيمة نصية في `RECIEVE_REPORT.REPORTING_SORT`!) |
| `AUTHORITY` | text | جهة البلاغ — مصدر قيم قائمة الجهات |
| `IS_INTERNET` | boolean | السجلات ذات `false` فقط تظهر في وحدة البلاغات |

### 2.3 `REPORT_AUTHS` — جهات الإخطار المرتبطة بالبلاغ

Migration: `2026_01_01_000016_create_report_auths_table.php` — Model: `app/Models/ReportAuth.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `ID` | bigIncrements | PK |
| `REPORT_ID` | unsignedBigInteger, nullable | → `RECIEVE_REPORT.ID` (بدون DB constraint) |
| `AUTHORITY_ID` | text, nullable | **رغم الاسم، يخزَّن اسم الجهة نصًا** وليس مفتاحًا (انظر §9) |

### 2.4 `NotifiedAuthTBL` — دليل جهات الإخطار

Migration: `2026_01_01_000010_create_notifiedauthtbl_table.php` — Model: `app/Models/NotifiedAuth.php`

| العمود | النوع |
|---|---|
| `ID` | bigIncrements |
| `Notified_Auth` | string(500), nullable |

### 2.5 `Attachment_Tbl` — مرفقات البلاغ

Migration: `2026_01_01_000015_create_attachment_tbl_table.php` — Model: `app/Models/Attachment.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `AttachmentID` | increments | PK |
| `AttachmentName` | string(255) | نوع المرفق: `صورة البلاغ` أو `صورة متابعة البلاغ` فقط (مقيّد في Validation) |
| `ReportID` | unsignedBigInteger | → البلاغ |
| `FilePath` | string(500) | المسار على disk الـ `public` تحت مجلد `attachments/` |
| `FileExtension` | string(10) | |

### 2.6 `INJURED` و `DEATHS` — المصابون والوفيات

Migrations: `2026_01_01_000014_create_injured_table.php`، `2026_01_01_000013_create_deaths_table.php` — Models: `app/Models/Injured.php`، `app/Models/Death.php`

- `INJURED`: `INJURED_ID` PK، `INJURED_NAME` (50)، `INJURED_AGE` (صغير — تغيّر من date إلى `unsignedTinyInteger` في `2026_08_10_135607_*`)، `INJURED_DIAGNOSIS` (500)، `INJURED_FOLLOWUP` (text)، `REPORT_ID` + **FK حقيقي** → `RECIEVE_REPORT.ID`.
- `DEATHS`: `Deceased_ID` PK، `Deceased_NAME` (50)، `Deceased_AGE` (نفس التغيير في `2026_08_10_135840_*`)، `Deceased_ADDRESS`، `Deceased_FOLLOWUP`، `REPORT_ID` + **FK حقيقي**.

### 2.7 تنبيه: الفرق عن وحدة الإشارات / Signals ≠ Reports

| | البلاغات (Reports) | الإشارات (Signals) |
|---|---|---|
| جدول الكيان | `RECIEVE_REPORT` | `MAIN_SIGNAL` + `SEND_SIGNAL` ... |
| مصدر "جهة" | `REPORTING_TYPES.AUTHORITY` | جدول `AUTHORITY` المنفصل |
| مرجع النوع | `REPORTING_SORT` → `REPORTING_TYPES` | `SignalContent`/`SignalUnit` |

المصطلح الإنجليسي في الكود مكسور عمديًا (`Recieve` بدل `Receive`) للتوافق مع أسماء الجداول القديمة — **لا تصحّحه إلا مع migration كاملة**.

### 2.8 علاقات Model ER / Relationships

من `app/Models/RecieveReport.php:39-82`:

```
RecieveReport ─┬─ user()            belongsTo User          عبر REPORT_RECIPIENT → id
               ├─ city()            belongsTo City          عبر CITY → CITY_ID
               ├─ village()         belongsTo Village       عبر VILLAGE → VILLAGE_ID
               ├─ reportingType()   belongsTo ReportingType عبر REPORTING_SORT → REPORT_ID
               ├─ deaths()          hasMany Death           عبر REPORT_ID → ID
               ├─ injuries()        hasMany Injured         عبر REPORT_ID → ID
               ├─ attachments()     hasMany Attachment      عبر ReportID → ID
               ├─ reportAuths()     hasMany ReportAuth      عبر REPORT_ID → ID
               └─ lockedByUser()    belongsTo User          عبر LOCKED_BY → id
```

---

## 3. دورة حياة البلاغ / Status Workflow

الحالات معرّفة كـ `$statuses` في `app/Http/Controllers/ReportController.php:31-36` كنصوص عربية **خالية (raw strings)** — لا جدول ولا enum:

```
تم استلام البلاغ  ──►  قيد المعالجة  ──►  تم التنفيذ  ──►  تم الانتهاء (قفل)
      ▲                                                        │
      └──────────────────── إعادة فتح ◄─────────────────────────┘
```

| الحالة | badge في الواجهة | القفل/الإعادة فتح |
|---|---|---|
| `تم استلام البلاغ` | أحمر | الحالة الافتراضية عند `store()` |
| `قيد المعالجة` | أصفر | — |
| `تم التنفيذ` | أخضر | — |
| `تم الانتهاء` | أزرق | **حالة قفل خاصة** |

**آلية القفل (Locking):**
1. اختيار `تم الانتهاء` متاح فقط لأصحاب دور `مشرف عام` أو `ادمن` — راجع `update()` في `ReportController.php:219-228` والواجهة `resources/views/reports/edit.blade.php:215-219` (إخفاء الخيار من الـ select).
2. عند أول انتقال للقفل: تُملأ `REPORT_END_DATE` و`REPORT_END_TIME` بـ `now()` ويُسجَّل `LOCKED_BY = auth()->id()` (`ReportController.php:317-321`).
3. أثناء القفل: `update()` يعيد 403 لغير ذوي الصلاحية (`:221-223`)، والشاشة تصبح read-only عبر `$isLocked` + `@disabled($isLocked)` (`edit()` عند `:175`، والواجهة `:61`).
4. إعادة الفتح (أي حالة غير `تم الانتهاء`) تُفرّغ `REPORT_END_DATE/TIME` و`LOCKED_BY` (`:322-327`).

**"منتهي" في الإحصائيات** = `REQUEST_STATUS = 'تم الانتهاء'` (المرجع في `MonthlyReportStatsController.php:24-29` و`ReportDashboardController.php:48,213`).

**تدفق "جهات الإخطار" (لا يوجد إشعار فعلي):**
1. إدارة القائمة من `settings/notified-auths` (جدول `NotifiedAuthTBL`).
2. في إنشاء/تعديل البلاغ: قائمة checkbox قابلة للبحث (`reports/create.blade.php:231-245`، `reports/edit.blade.php:324-345`).
3. الاختيارات تُحفظ كصفوف في `REPORT_AUTHS` (`ReportAuth::create` مع `AUTHORITY_ID` = **اسم الجهة** — `store()` عند `:735-740`، ويُعاد بناؤها بالحذف وإعادة الإنشاء في `update()` عند `:331-338`).
4. حقل نصي `NOTIFIED_AUTHORITIES` ("الإجراءات المتخاذة"، ≤5000 حرف) يُملأ في التعديل فقط (`update()` `:303`) ويظهر في شاشة العرض (`show.blade.php:47-49`).

لا يوجد أي Job/Mail/Push — "الإخطار" تسجيل اختيار فقط.

---

## 4. المسارات / Routes

كلها في `routes/web.php` داخل `Route::middleware(['auth'])->group(...)` (سطر 54). الملفات الأخرى (`routes/auth.php`, `routes/console.php`) لا تحتوي مسارات للبلاغات.

### 4.1 CRUD الأساسي — `ReportController`

| الطريقة | URI | Action | الاسم | الحماية |
|---|---|---|---|---|
| GET | `/reports` | `index` | `reports.index` | `auth` فقط |
| GET | `/reports/create` | `create` | `reports.create` | `auth` فقط |
| POST | `/reports` | `store` | `reports.store` | `auth` فقط |
| GET | `/reports/{report}` | `show` | `reports.show` | `auth` فقط |
| GET | `/reports/{report}/edit` | `edit` | `reports.edit` | `auth` فقط |
| PUT | `/reports/{report}` | `update` | `reports.update` | `auth` فقط |
| DELETE | `/reports/{report}` | `destroy` | `reports.destroy` | `auth` فقط |

> ⚠️ **لا يوجد `permission:` middleware على هذه المسارات** — الحماية على مستوى الـ route هي `auth` فقط، والحماية الفعلية عبر `@can` في الواجهة (§8).

### 4.2 التصدوير ومساعدات AJAX

| الطريقة | URI | Action | الاسم |
|---|---|---|---|
| GET | `/reports/export/excel` | `exportExcel` | `reports.export.excel` |
| GET | `/reports/export/pdf` | `exportPdf` | `reports.export.pdf` |
| GET | `/reports/report-types-by-auth` | `reportTypesByAuth` (JSON) | `reports.report-types-by-auth` |
| GET | `/reports/villages-by-city/{city}` | `villagesByCity` (JSON) | `reports.villages-by-city` |

**الترتيب مهم:** `monthly-stats` و`dashboard` تُعرَّف **قبل** `reports/{report}` حتى لا يفسّرها Laravel كمعرّف رقمي (تعليق في `web.php:68`).

### 4.3 المرفقات — مسارات متداخلة

| الطريقة | URI | Action |
|---|---|---|
| GET | `/attachments/{attachment}` | `AttachmentController@show` |
| GET | `/reports/{report}/attachments/create` | `ReportController@createAttachment` |
| POST | `/reports/{report}/attachments` | `ReportController@storeAttachment` |
| DELETE | `/reports/{report}/attachments/{attachment}` | `ReportController@destroyAttachment` |

### 4.4 الإحصائيات الشهرية — محميّة بصلاحية

| الطريقة | URI | Action | Middleware |
|---|---|---|---|
| GET | `/reports/monthly-stats` | `MonthlyReportStatsController@index` | `permission:reports.monthly-stats` |
| GET | `/reports/monthly-stats/export/excel` | `@exportExcel` | `permission:reports.monthly-stats` |
| GET | `/reports/monthly-stats/export/pdf` | `@exportPdf` | `permission:reports.monthly-stats` |

### 4.5 لوحة التحكم — `auth` فقط

| الطريقة | URI | Action | الاسم |
|---|---|---|---|
| GET | `/reports/dashboard` | `ReportDashboardController@index` | `reports.dashboard` |
| GET | `/reports/dashboard/export/cities/excel` | `@exportCitiesExcel` | `reports.dashboard.export.cities.excel` |
| GET | `/reports/dashboard/export/cities/pdf` | `@exportCitiesPdf` | `reports.dashboard.export.cities.pdf` |
| GET | `/reports/dashboard/export/authorities/excel` | `@exportAuthoritiesExcel` | `reports.dashboard.export.authorities.excel` |
| GET | `/reports/dashboard/export/authorities/pdf` | `@exportAuthoritiesPdf` | `reports.dashboard.export.authorities.pdf` |

### 4.6 مسارات الإعدادات — بادئة `settings.` (web.php:305-369)

مجموعة: `Route::prefix('settings')->name('settings.')->middleware(['auth'])`.

**أنواع البلاغات وجهات البلاغ** (`ReportingTypeController`):

| الطريقة | URI | الاسم | Middleware |
|---|---|---|---|
| GET | `settings/reporting-types` | `index` | `can:reports.view` |
| GET / POST | `settings/reporting-types/create` / `` | `create` / `store` | `can:reports.create` |
| GET / PUT | `settings/reporting-types/{reportingType}/edit` / `` | `edit` / `update` | `can:reports.edit` |
| DELETE | `settings/reporting-types/{reportingType}` | `destroy` | `can:reports.delete` |

**جهات الإخطار** (`NotifiedAuthController`, web.php:356-369): نفس النمط بالضبط (`can:reports.view/create/edit/delete`).

### 4.7 مسارات مكررة (Dead duplicates)

`web.php:115-119` تعيد تعريف مساري AJAX اللذين أُضيفا بالفعل في `:63-66` (نفس الـ URI ونفس الأسماء). Laravel يتجاهل المكرر، لكن حذف النسخة المكررة تحسّن للصيانة (§12).

---

## 5. منطق Controllers / Controller Logic

### 5.1 `ReportController` (app/Http/Controllers/ReportController.php — 882 سطر)

**دوال مساعدة خاصة:**

| الدالة | الأسطر | الوظيفة |
|---|---|---|
| `authorityOptions()` | 47–57 | قيم `AUTHORITY` المميزة من `REPORTING_TYPES` حيث `IS_INTERNET = false`، مقصوصة في PHP (لا SQL) عمدًا لتطابق ما يرسله TrimStrings byte-for-byte مع `<option>`، وتعتمد `utf8mb4_unicode_ci PAD SPACE` |
| `typeOptionsFor(?string $auth)` | 64–74 | أنواع بلاغ جهة معيّنة: `whereRaw('TRIM(AUTHORITY) = ?')` + `IS_INTERNET=false` مرتّبة بـ `REPORT_SORT` |
| `currentAuthority(RecieveReport)` | 82–87 | جهة البلاغ للعرض: `reportingType.AUTHORITY` أولًا، والقيمة المخزّنة `REPORTING_Auth` مجرد fallback — نفس منطق الفلاتر والتصدير |

**الدوال العامة:**

| الدالة | الأسطر | الملخص |
|---|---|---|
| `index(Request)` | 89–114 | Paginate (20 صفحة) عبر `filteredQuery()` — يمرر المدن/الجهات/الأنواع/الحالات لـ `reports.index`. **كود ميت** في `:101-113` (فلتر `search` بعد `return` — §12) |
| `filteredQuery(Request)` | 120–154 | استعلام مشترك بين القائمة والتصدير — الفلاتر: `reporter_name` LIKE، `national_id` → `REPORTER_SSN` LIKE، `phone` → `REPORT_FOLLOWUP_NUMBER` LIKE، `city`، `village` أو `madina` (الأولوية للقرية)، `reporting_auth` (مطابقة TRIM على `REPORTING_Auth` **أو** جهة النوع)، `reporting_sort`، `status` (scalar أو array → `whereIn`)، `date_from/to` على `REPORT_START_DATE`، وفلتر وقت اختياري. ترتيب `latest('ID')` |
| `show(RecieveReport)` | 159–164 | يحمّل `reportingType, city, village, deaths, injuries, attachments, reportAuths, lockedByUser` → `reports.show` |
| `edit(RecieveReport)` | 169–215 | `$canLockReport = hasAnyRole(['مشرف عام','ادمن'])` (`:172`)؛ `$isLocked` = الحالة `تم الانتهاء` && !canLock (`:175`)؛ يحمّل جهات الإخطار (المحددة أولًا)، الجهات الحالية مع إعادة تضمين الأيتام (`:189-199`)؛ `$canEditDateTime = hasRole('مشرف عام')` (`:205`) |
| `update()` | 217–377 | راجع §6 — أهم دالة في الموديول |
| `destroy()` | 382–410 | Transaction: حذف الإصابات، الوفيات، `reportAuths`، ملفات المرفقات من disk الـ `public` + الصفوف، ثم البلاغ؛ ثم سجل `SystemRecord` (`:397-406`) |
| `storeAttachment()` | 423–485 | راجع §7 |
| `destroyAttachment()` | 490–514 | `abort_unless` تأكيد ملكية المرفق للبلاغ (`:493`)؛ حذف ملف + صف + سجل تدقيق |
| `exportExcel()` | 519–524 | `filteredQuery()->get()` → `ReportsExport` (Maatwebsite Excel) باسم `البلاغات.xlsx` |
| `exportPdf()` | 529–598 | نفس الاستعلام؛ إعادة تشكيل الحروف العربية عبر ArPHP (`:534-565`) ثم dompdf `reports.export-pdf` (A4 landscape، رؤوس معكوسة RTL)، تنزيل `البلاغات.pdf` |
| `create()` | 604–619 | نموذج الإنشاء مع الجهات/المدن/جهات الإخطار + `nextRegisterNumber` + `canEditDateTime` |
| `reportTypesByAuth()` | 621–631 | JSON: `{REPORT_ID, REPORT_SORT}` لجهة محددة (trim match، non-internet) |
| `villagesByCity()` | 633–657 | JSON: قرى مركز؛ `type=مدينة` يطابق `VILLAGE_SORT='مدينة'`، وإلا `!= 'مدينة'`، ويشمل `PARENT_NAME` |
| `store()` | 659–779 | راجع §6 |
| `buildNextRegisterNumber()` | 781–788 | `REP-` + (أعلى لاحقة رقمية في `REPORT_REGISTER_NUMBER` + 1) |
| `validateCountsAgainstRows()` | 796–818 | hook `after()`: الأعداد لا تقل عن عدد الصفوف المسماة |
| `reportValidationMessages()` | 823–845 | رسائل خطأ عربية مخصصة |
| `reportValidationAttributes()` | 850–881 | تسميات حقول عربية |

### 5.2 `ReportDashboardController` (382 سطر)

| الدالة | الأسطر | الملخص |
|---|---|---|
| `index(Request)` | 19–197 | فلاتر: `from, to, city, type, status, village_center` — يحسب: الإجمالي (مفلتر)، اليوم/الأسبوع (غير مفلتر)، نسبة الإنجاز (`تم الانتهاء`/الإجمالي ×100، `:48-49`)، سلسلة يومية 30 يومًا (`:51-55`)، أعلى 10 مدن (`:57-61`)، عدد لكل نوع (`:63-66`)، مجموع `Deceased_Num`/`INFECTED_NUM` (`:68-69`)، جدول أداء المدن (`buildCityPerformance`، `:71`)، مجموعات Chart.js، مصفوفة جغرافية مركز×نوع (أعلى 12 مركز / 8 أنواع، `:91-143`)، رسم القرى داخل المركز (`:148-178`)، وإحصائيات الجهات (`buildAuthorityStats`، `:181`) |
| `buildCityPerformance()` | 202–235 | لكل مدينة: `withCount` للإجمالي + كل حالة (مفلتر بالتاريخ)، يحسب `completion_rate`، ترتيب تنازلي [نسبة، منتهية] |
| `buildAuthorityStats()` | 240–281 | `groupBy REPORTING_Auth × VILLAGE × REPORTING_SORT` — لكل جهة: الإجمالي، عدد القرى، أعلى 3 قرى، أعلى 3 أنواع (مفصولين بـ "، ") |
| `exportCitiesExcel/Pdf` | 286–332 | `CityPerformanceExport` / dompdf `reports.dashboard-cities-pdf` |
| `exportAuthoritiesExcel/Pdf` | 337–381 | `AuthorityStatsExport` / dompdf `reports.dashboard-authorities-pdf` |

### 5.3 `MonthlyReportStatsController` (190 سطر)

- ثوابت: `MONTHS` (أسماء الأشهر العربية، `:19-22`)، `STATUSES` (مفاتيح: received/processing/executed/finished، `:24-29`).
- `buildStats(month, year)` (`:36-81`): لكل `City` عدد حسب `whereYear/whereMonth(REPORT_START_DATE)` للإجمالي + الحالات الأربع؛ `completion_rate = منتهية/إجمالي×100`؛ ترتيب تنازلي؛ صفوف مجاميع.
- `index()` (`:83-121`): الشهر/السنة الافتراضيان = الحالي؛ قائمة سنوات من البيانات؛ view `reports.monthly-stats` مع بيانات أعمدة مكدّسة + دائرة الحالات.
- `exportExcel()` (`:126-136`): `CityStatsExport` → `إحصائيات-المراكز-{month}-{year}.xlsx`.
- `exportPdf()` (`:141-189`): ArPHP + dompdf `reports.monthly-stats-pdf` (A4 landscape) مع صف المجاميع و"ملخص الأداء".

### 5.4 `ReportingTypeController` / `NotifiedAuthController`

CRUD بسيط (resource تقليدي) — كل دالة paginate 20 ورسائل نجاح عربية. **`destroy` لا يتحقق من وجود بلاغات تستخدم النوع** (`ReportingTypeController.php:53-60`) — حذف نوع مُشار إليه من بلاغات يترك `REPORTING_SORT` يتيمًا (§12).

### 5.5 `AttachmentController` (48 سطر)

`show(Attachment)` (`:17-35`): يقدّم الملف من disk الـ `public` عبر controller route (يعمل حتى بدون symlink `public/storage` — تعليق في `:14-15`)، 404 لو الملف مفقود، يقدّم **inline** لـ jpg/jpeg/png/gif/webp/bmp/pdf (`isInlineable`، `:40-47`) وإلا تحميل، مع `X-Content-Type-Options: nosniff`. اسم الملف = `AttachmentName` + `.` + `FileExtension`.

---

## 6. منطق التسجيل والتعديل / Store & Update Flows

### 6.1 `store()` (الإنشاء، الأسطر 659–779)

1. **Validation** موحّدة مع `update()` (§7) بدون status/تاريخ-وقت القابلية للتعديل.
2. **`مشرف عام` فقط** يمكنه تعيين تاريخ/وقت تقديم مخصّص؛ غيره يحصل على `now()` (`:703-705`).
3. **Transaction** (`:709–760`):
   - إنشاء `RecieveReport` مع `REPORT_RECIPIENT = auth()->id()`، الحالة الابتدائية `تم استلام البلاغ`، `IS_LOCKED = false`، `NOTIFIED_AUTHORITIES = null`، ورقم القيد من `buildNextRegisterNumber()`.
   - إنشاء صفوف `ReportAuth` (جهات الإخطار المحددة)، `Injured`، `Death`.
4. سجل تدقيق `SystemRecord` (`:765-774`).
5. Redirect بنجاح: "تم تسجيل البلاغ بنجاح برقم …".

### 6.2 `update()` (التعديل، الأسطر 217–377)

1. `abort 403` إن كان البلاغ مقفلًا ولم يكن المستخدم `مشرف عام`/`ادمن` (`:221-223`).
2. `$allowedStatuses` يستثني `تم الانتهاء` لغير ذوي صلاحية القفل (`:226-228`).
3. **Validation** inline (`Validator::make`، `:230-272`) — راجع §7.
4. **`مشرف عام` فقط** يغيّر التاريخ/الوقت (`:274-276`).
5. **Transaction** (`:284–363`):
   - تحديث البلاغ — الأعداد تسقط إلى عدد الصفوف المسماة إن كانت أقل (`:299-315`).
   - أول قفل: ملء `REPORT_END_DATE/TIME` + `LOCKED_BY` (`:317-321`)؛ إعادة فتح: تفريغها (`:322-327`).
   - إعادة بناء صفوف `ReportAuth` + `Injured` + `Death` (حذف الكل + إعادة إنشاء، `:331-362`).
6. سجل تدقيق `SystemRecord` (`:365-374`) ثم redirect.

> ⚠️ `LOCKED_BY` **غير موجود في `$fillable`** في `app/Models/RecieveReport.php:14-37` رغم تمريره في مصفوفة `update()` — قد يُسقط تلقائيًا (§12).

---

## 7. قواعد التحقق من المدخلات / Validation

الكيان الأساسي يستخدم **inline `Validator::make`** داخل `ReportController` (وليس FormRequests). القواعد الرئيسية:

| القاعدة | الشرط |
|---|---|
| `REPORTER_NAME` | required, max:50 |
| `REPORT_FOLLOWUP_NUMBER` (هاتف) | regex: `^(01[0125]\d{8}\|0\d{9})$` |
| `REPORTER_SSN` | digits:14 + regex: `^[23]\d{13}$` |
| `REPORTING_Auth` | يجب أن يكون من `authorityOptions()` |
| `REPORTING_SORT` | يجب أن يوجد في `REPORTING_TYPES` **لجهة البلاغ المُرسلة** (`:239-243`) |
| `CITY` / `VILLAGE` | `exists` |
| `location_type` | `in:مدينة,قرية` |
| `X_AXIS` / `Y_AXIS` | numeric |
| `PLACE_Accident` | max:500 |
| `DAMAGE` | **required** |
| `REQUEST_STATUS` | ضمن `$allowedStatuses` (§6.2) |
| `notified_authorities[]` | array of strings |
| `NOTIFIED_AUTHORITIES` | max:5000 ("الإجراءات المتخذة") |
| صفوف `injured[i][...]` | تحقق لكل صف — **مفتاح العمر يختلف**: `birth_date` في `store()` (`:686`) مقابل `age` في `update()` (`:257`) |
| صفوف `deaths[i][...]` | نفس الاختلاف (`birth_date` في `:693` مقابل `age` في `:264`) — لا توحيد بين المسارين |
| `INFECTED_NUM` / `Deceased_Num` | عدد صحيح ≥ 0 + hook بعد: ≥ عدد الصفوف المسماة (`validateCountsAgainstRows()`، `:796-818`) |

رسائل الخطأ والتسميات العربية: `reportValidationMessages()` (`:823-845`) و`reportValidationAttributes()` (`:850-881`).

**FormRequests للإعدادات فقط** (`authorize(): true` في الجميع):

| الملف | القواعد |
|---|---|
| `app/Http/Requests/StoreReportingTypeRequest.php` / `UpdateReportingTypeRequest.php` | `REPORT_SORT`: required\|string\|max:500؛ `AUTHORITY`: required\|string\|max:500؛ `IS_INTERNET`: sometimes\|boolean — **بدون قاعدة unique** |
| `app/Http/Requests/StoreNotifiedAuthRequest.php` / `UpdateNotifiedAuthRequest.php` | `Notified_Auth`: required\|string\|max:500 |

**رفع المرفقات** (`storeAttachment()`، `:423-434`):
- `AttachmentName`: `in:صورة البلاغ,صورة متابعة البلاغ`
- `attachment`: array, min:1 — كل ملف ≤ 102400 KB (100MB) — **أي امتداد** (لا تقييد بصيغة)
- داخل Transaction: حفظ الملفات في `attachments/` على disk `public` + إنشاء صفوف؛ عند الفشل حذف الملفات المحفوظة + flash أخطاء (`:466-478`).

---

## 8. الصلاحيات والأدوار / Permissions & Roles

النظام: **Spatie Permission** (`User` يستخدم `HasRoles` في `app/Models/User.php:15`)، وأسماء middleware المسجلة في `bootstrap/app.php:14-18` (`role`, `permission`, `role_or_permission`). لا توجد `Gate::define` أو Policies للبلاغات (`app/Policies/` يحوي `TaskAssignmentPolicy` فقط).

### 8.1 صلاحيات الموديول (seeded في `database/seeders/RolesAndPermissionsSeeder.php:21-26`)

| الصلاحية | الاستخدام الفعلي |
|---|---|
| `reports.view` | إعدادات الأنواع/الجهات + شاشات القائمة في الـ sidebar |
| `reports.create` | زر إضافة بلاغ |
| `reports.edit` | أزرار التعديل |
| `reports.delete` | زر الحذف |
| `reports.monthly-stats` | فقط صلاحية تُفحص فعليًا كـ `permission:` middleware (web.php:69-79) — مُمنوحة لدور `مشرف عام` في migration: `2026_09_08_000001_add_reports_monthly_stats_permission.php` |
| `reports.lock` | **مُعرَّفة لكن لا تُفحص في أي كود** — القفل يعتمد على اسم الدور (§12) |

### 8.2 الأدوار (`RolesAndPermissionsSeeder.php:66-100`)

| الدور | صلاحيات البلاغات |
|---|---|
| `مشرف عام` | كل الصلاحيات (تُمنح `Permission::all()`) + القفل + تعديل التاريخ/الوقت |
| `موظف استقبال بلاغات` | `reports.view`, `reports.create`, `reports.edit` |
| `مستخدم عادي` | `reports.view` فقط |
| `موظف إشارات` / `موظف تمام` | لا صلاحيات بلاغات |

> اسم دور `ادمن` يُفحص في `hasAnyRole(['مشرف عام','ادمن'])` (`ReportController.php:172,219`) لكنه **غير مُعرَّف في الـ seeder** — دور قديم/يدوي.

### 8.3 فجوات الحماية (Security Notes)

1. مسارات CRUD الأساسية (§4.1) و`dashboard` محميّة بـ `auth` فقط — أي مستخدم مسجّل الدخول يستطيع الوصول على مستوى الـ route، والحماية الفعلية `@can` في Blade فقط.
2. الـ sidebar (`resources/views/partials/sidebar.blade.php:41-99`) يستخدم `@canany(['reports.create','reports.view'])` وأدوار فرعية `@can('reports.create'/'reports.view')` — الإحصائيات الشهرية (`:79`) ولوحة التحكم (`:88`) معروضة بدون قيد `@can` مرتبط بالـ middleware.
3. `hasAnyRole`/`hasRole` مكتوبة hard-coded بأسماء عربية داخل `ReportController` — يتعارض مع فصل الأدوار عن المنطق.

---

## 9. التصدير / Exports

### 9.1 `app/Exports/ReportsExport.php` (85 سطر) — قائمة البلاغات

Maatwebsite Excel — `FromCollection, WithHeadings, WithMapping, WithStyles`:
- **16 عمود عربي** (`:29-49`): الرقم القومي، رقم قيد البلاغ، متلقي البلاغ، اسم المبلغ، جهة البلاغ، نوع البلاغ، المركز، مكان البلاغ، تاريخ تقديم البلاغ، وقت تقديم البلاغ، عدد المصابين، عدد الوفيات، حالة البلاغ، تاريخ انتهاء البلاغ، وقت انتهاء البلاغ، رقم تليفون.
- `map()` (`:51-71`): جهة البلاغ مع fallback لنوع البلاغ، اسم المتلقي (أو `online` إن null).
- تنسيق: عريض أبيض على `#001F3F` (`:76-84`).

### 9.2 التصديرات الأخرى

| الملف | الاستخدام | الأعمدة |
|---|---|---|
| `app/Exports/CityStatsExport.php` | الإحصائيات الشهرية Excel | م، المركز/المدينة، إجمالي البلاغات، الحالات الأربع، نسبة التنفيذ + **صف مجاميع** (`:59-68`) |
| `app/Exports/CityPerformanceExport.php` | لوحة التحكم — أداء المدن Excel | الأعمدة نفسها مع "نسبة الإنجاز"، بدون صف مجاميع |
| `app/Exports/AuthorityStatsExport.php` | لوحة التحكم — إحصائية الجهات Excel | م، جهة البلاغ، عدد البلاغات، عدد القرى، أكثر قرية، أكثر نوع بلاغ |

### 9.3 PDF

كل PDF تستخدم **dompdf** (`Barryvdh\DomPDF`) + إعادة تشكيل الحروف العربية بـ **ArPHP** (معالجة glyph shapes قبل التوليد، لتعمل العربية في dompdf)، مع **رؤوس جدول معكوسة** لدعم RTL وصفحات A4 landscape. قوالب Blade:

- `resources/views/reports/export-pdf.blade.php` — قائمة البلاغات
- `resources/views/reports/monthly-stats-pdf.blade.php` — الإحصائيات الشهرية (+ ملخص الأداء)
- `resources/views/reports/dashboard-cities-pdf.blade.php` — أداء المدن
- `resources/views/reports/dashboard-authorities-pdf.blade.php` — إحصائية الجهات

---

## 10. واجهات المستخدم / Views (Blade)

لا توجد مكوّنات Livewire/Volt للبلاغات — Blade كلاسيكي خالص.

| الملف | الأسطر | الوظيفة |
|---|---|---|
| `resources/views/reports/index.blade.php` | 477 | القائمة: أزرار Excel/PDF (`:10-17`)، لوحة فلاتر قابلة للطي (اسم، رقم ق، تليفون، مركز، قرية، جهة، نوع، حالة، نطاق تاريخ، فلتر وقت اختياري، `:34-168`)، تبويبات الحالة "كل البلاغات"/"المستلمة + قيد المعالجة" (`:170-188`)، الجدول مع شارات ملوّنة حسب الحالة (`:221-227`) وأزرار إجراءات (`:236-273`)، pagination |
| `resources/views/reports/create.blade.php` | 604 | نموذج جديد: رقم قيد تلقائي (`:17-19`)، بيانات المبلّغ مع toggle موبايل/أرضي (`:33-68`)، تاريخ/وقت read-only لغير `مشرف عام` (`:71-99`)، **cascading selects**: جهة ← نوع (AJAX) ومركز ← قرية، إحداثيات، أضرار، صفوف إصابات/وفيات ديناميكية (`:261-304`)، جهات إخطار checkbox مع بحث (`:231-245, 428`)، مزامنة الأعداد في JS (`:441-500`) |
| `resources/views/reports/show.blade.php` | 130 | العرض: شارة الحالة (`:14-23`)، بيانات المبلّغ/الجهة/النوع/المكان/الأضرار/الإجراءات (`:26-50`)، جدولا الإصابات والوفيات (`:52-88`)، المرفقات (`:90-122`) |
| `resources/views/reports/edit.blade.php` | 715 | التعديل: ترويسة "عرض" عند القفل (`:13`)، بانر قفل مع تاريخ ومن قفله (`:26-47`)، fieldset `@disabled($isLocked)` (`:61`)، التاريخ/الوقت لـ `مشرف عام` فقط (`:104-115`)، القائمة المنسدلة تخفي `تم الانتهاء` إلا لـ `$canLockReport` (`:215-219`)، صفوف موجودة + قوالب (`:255-320`)، جهات إخطار محددة مسبقًا (`:324-345, 545`) |
| `resources/views/reports/attachments-create.blade.php` | 180 | رفع متعدد: نوع مقصور على صنفين (`:31-40`)، dropzone + تنبيهات 100MB في الواجهة (`:74-177`)، منع الإرسال المزدوج |
| `resources/views/reports/dashboard.blade.php` | 542 | لوحة التحكم: فلاتر (`:10-63`)، 5 بطاقات KPI (إجمالي، اليوم، الأسبوع، نسبة الإنجاز، الضحايا، `:65-87`)، رسم يومي 30 يومًا، أعمدة المدن + دائرة الأنواع، جدول أداء المدن + تصدير، رسم القرى، إحصائية الجهات، المصفوفة الجغرافية، سكربتات Chart.js 4.4.1 |
| `resources/views/reports/monthly-stats.blade.php` | — | فلتر شهر/سنة، جدول إحصائيات + روابط تصدير، أعمدة مكدّسة + دائرة حالات |
| `resources/views/settings/reporting-types/{index,create,edit}.blade.php` | — | إعدادات الأنواع — الأزرار محميّة `@can('reports.edit'/'reports.delete')` |
| `resources/views/settings/notified-auths/{index,create,edit}.blade.php` | — | إعدادات جهات الإخطار — نفس النمط |

**التنقل:** `resources/views/partials/sidebar.blade.php:41-99` — dropdown "البلاغات" (`@canany(['reports.create','reports.view'])`) يحوي: إضافة بلاغ (`@can('reports.create')`)، تقارير البلاغات (`@can('reports.view')`)، إحصائيات شهرية، لوحة تحكم. الإعدادات: `:430-500` (`@can('reports.view')`).

---

## 11. التدقيق / Audit Trail

كل عملية create/update/delete على البلاغ أو مرفقاته تكتب صفًا في `SystemRecord` (اسم المستخدم، الجهاز، IP، عنوان، وصف، تاريخ):

- `update()` → `ReportController.php:365-374`
- `destroy()` → `:397-406`
- `destroyAttachment()` → `:501-510`
- `store()` → `:765-774`

---

## 12. المعالم المعروفة / Known Issues & Tech Debt

مفهرسة من أقصى أهمية إلى أقل، مع مواقع محددة في الكود:

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 1 | **`LOCKED_BY` خارج `$fillable`** رغم تمريره في `update()` | `app/Models/RecieveReport.php:14-37` مقابل `ReportController.php:317-321` | قد لا يُحفظ عمود القفل أبدًا (silently dropped) — يحتاج `$guarded` أو إضافته للـ fillable |
| 2 | **المسارات الأساسية بلا `permission:` middleware** — `auth` فقط | `routes/web.php:55-57, 109-112, 81-94` | أي مسجّل دخول يمكنه POST/PUT/DELETE على `/reports` مباشرة بتجاوز الواجهة |
| 3 | **`reports.lock` مُعرَّفة ولا تُفحص** — القفل يعتمد `hasAnyRole(['مشرف عام','ادمن'])` hard-coded | Seeder: `RolesAndPermissionsSeeder.php:25`؛ الاستخدام: `ReportController.php:172,219` | تعارض بين التصميم (Spatie) والتنفيذ (أسماء أدوار) |
| 4 | **كود ميت: فلتر البحث `search`** بعد `return` في `index()` — حقل البحث في الواجهة لا يفعل شيئًا | `ReportController.php:101-113`؛ الواجهة `index.blade.php:153-167` | ميزة معطلة منذ كتابتها |
| 5 | **ثغرة سباق (race) في رقم القيد**: `MAX` + 1 بدون ضمان فريد على مستوى DB | `ReportController.php:781-788` | تسجيلان متزامنان قد يحصلان على نفس `REP-n` — يحتاج unique index + معالجة تعارض |
| 6 | **`destroy` في `ReportingTypeController` بلا فحص اعتماد**: حذف نوع يترك `REPORTING_SORT` يتيمًا | `ReportingTypeController.php:53-60` | سقوط في `reportingType?->AUTHORITY` fallback → جهة قد تختفي من العرض |
| 7 | **`IS_LOCKED` عمود مهجور**: الكود كله يعتمد `REQUEST_STATUS = 'تم الانتهاء'` | العمود في migration `..._create_recieve_report_table.php:31` | حياد بين البيانات والمنطق |
| 8 | **مسارات AJAX مكررة** بنفس الـ URI والاسم | `routes/web.php:63-66` مكرر في `:115-119` | حشو — حذف نسخة |
| 9 | **لا قاعدة `unique` على `ReportingType`** (النوع/الجهة) | `Store/UpdateReportingTypeRequest` | يمكن إدخال صفوف مكررة تظهر كخيارات مكررة |
| 10 | **رفع أي امتداد** بحد 100MB فقط — لا allowlist لصيغ الملفات | `ReportController.php:425-434` | خطر رفع ملفات تنفيذية إلى `public` disk (رغم أن `AttachmentController` يحدّد Content-Type) |
| 11 | **`db:seed` لا تمنح `ادمن`** أي صلاحيات — دور غير مُعرَّف في الـ seeder | `RolesAndPermissionsSeeder.php` | `hasAnyRole(['مشرف عام','ادمن'])` تفحص دورًا قد لا يملك صلاحيات |

---

## 13. مسرد المصطلحات / Glossary

| في الكود | المعنى |
|---|---|
| `RecieveReport` / `RECIEVE_REPORT` | البلاغ المستلم (هجاء قديم محفوظ للتوافق) |
| `REPORTING_SORT` | نوع البلاغ (اسم العمود مضلّل — يحوي `REPORT_SORT` النصي) |
| `REPORTING_Auth` | جهة البلاغ (نص fallback) |
| `REPORT_REGISTER_NUMBER` | رقم قيد البلاغ `REP-{n}` |
| `REPORT_FOLLOWUP_NUMBER` | رقم التليفون (رغم الاسم) |
| `INFECTED_NUM` | عدد المصابين |
| `Deceased_Num` | عدد الوفيات |
| `NOTIFIED_AUTHORITIES` | "الإجراءات المتخذة" (نص حر — **ليس** قائمة جهات الإخطار) |
| `REPORT_AUTHS` | صفوف جهات الإخطار المحددة للبلاغ |
| `IS_INTERNET` | فلتر: `false` = يظهر في البلاغات (الإشارات تدير `true` بشكل منفصل) |
