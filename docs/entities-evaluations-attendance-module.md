# وحدة الجهات / التقييمات / التمامات — Entities / Evaluations / Attendance Modules

> توثيق تقني ثنائي اللغة (عربي + مصطلحات إنجليزية) موجّه للمطورين.  
> آخر مراجعة ضد الكود: أكتوبر 2026.  
> ملاحظة: **التقييمات (Evaluations)** تستخدم **Livewire + Volt** بالكامل، بينما **التمامات (Attendance/الجهات)** تستخدم **Controllers كلاسيكية**.

---

## 1. نظرة عامة / Overview

هناك **ثلاث مفاهيم مختلفة لـ "جهة"** في المشروع (يجب عدم الخلط بينها):

| الموديل | الجدول | الموديول | ملاحظات |
|---|---|---|---|
| `App\Models\Entity` | `entities` | **التمامات (Attendance)** | قائمة القرى/الجهات المستخدمة في التمام اليومي |
| `App\Models\TaskEntity` | `task_entities` | **التكليفات (Tasks)** | منفصل تمامًا — موثّق في `app/Models/TaskEntity.php:13-15` |
| `App\Models\EvaluationEntity` | `evaluation_entities` | **التقييمات (Evaluations)** | منفصل تمامًا — يستخدم `SoftDeletes` |

**مسار الكود الرئيسي:**

```
routes/web.php (140-181, 208-222)                → المسارات
app/Http/Controllers/
  EntityController.php                            → CRUD الجهات + استيراد Excel
  EntityAttendanceDashboardController.php         → إحصائيات التمامات
  DailyAttendanceController.php                   → عرض التمامات اليومية + تنبيهات
  DailyAttendanceEntityController.php             → تسجيل done/not_done
  AttendanceTemplateController.php                → قوالب التمام + الدورات
app/Livewire/Evaluations/                          → وحدة التقييمات بالكامل
  DailyEvaluationForm.php / EntityManager.php / Dashboard.php / MonthlyCompletion.php
app/Console/Commands/GenerateDailyAttendances.php  → توليد تلقائي
app/Console/Commands/CalculateMonthlyEvaluations.php
```

---

## 2. مخطط قاعدة البيانات / Database Schema

### 2.1 التمامات (Attendance)

| الجدول | Migration | الأعمدة الرئيسية | ملاحظات |
|---|---|---|---|
| `attendance_templates` | `2026_08_05_142109_create_attendance_templates_table.php` + `2026_09_10_000000_add_frequency_to_attendance_templates_table.php` | `name`, `attendance_time`(time), `second_attendance_time`(time nullable), `script`(text), `daily_entities_count`, `frequency`(default daily), `frequency_config`(json nullable), `is_active` | `frequency`: daily/twice_daily/weekly/monthly/custom_dates |
| `entities` | `2026_08_05_142525_create_entities_table.php` + `2026_08_10_141224_add_main_location_to_entities_table.php` | `name`(**unique**), `main_location`(nullable) | **Hard delete** فقط، بلا `SoftDeletes` |
| `attendance_template_entities` | `2026_08_05_142533_create_attendance_template_entities_table.php` | `attendance_template_id` FK cascade, `entity_id` FK cascade | **unique**(`template_id`, `entity_id`) |
| `attendance_cycles` | `2026_08_05_142539_create_attendance_cycles_table.php` | `attendance_template_id` FK cascade, `cycle_number` | **unique**(`template_id`, `cycle_number`) |
| `daily_attendances` | `2026_08_05_142544_create_daily_attendances_table.php` + `2026_09_10_000001_add_time_slot_to_daily_attendances_table.php` + `2026_09_15_123434_fix_daily_attendances_unique_with_time_slot.php` | `attendance_template_id` FK, `attendance_cycle_id` FK, `attendance_date`(date), `time_slot`(tinyint default 1), `status` enum(`created,in_progress,completed`), `started_at`, `completed_at` | **unique**(`template_id`,`date`,`time_slot`). القيمه `waiting` موجوده في enum لكن **غير مستخدمة** |
| `daily_attendance_entities` | `2026_08_05_142551_create_daily_attendance_entities_table.php` | `daily_attendance_id` FK, `entity_id` FK, `status` enum(`pending,done,not_done`), `response_at` | **unique**(`daily_attendance_id`,`entity_id`) |

### 2.2 التقييمات (Evaluations)

| الجدول | Migration | الأعمدة الرئيسية | ملاحظات |
|---|---|---|---|
| `evaluation_entities` | `2026_08_08_000001_create_evaluation_entities_table.php` | `name`, `code`(unique nullable), `notes`, `is_active`(default true), timestamps, **softDeletes()** | كيان قابل للتفعيل/تعطيل |
| `evaluations` | `2026_08_08__000002_create_evaluations_table.php` *(double underscore)* | `evaluation_entity_id` FK cascade, `evaluated_by` FK→users cascade, `evaluation_date`(date), `response_type` enum(`head,deputy,operations,no_response`), `score`(unsignedTinyInteger), `note`(text nullable) | **unique**(`evaluation_entity_id`,`evaluation_date`). `score` يُحسب تلقائياً في الـ Model |
| `monthly_evaluations` | `2026_08_08_000003_create_monthly_evaluations_table.php` | `evaluation_entity_id` FK, `year`, `month`, `total_score`, `max_possible_score`, `percentage`(decimal 5,2), `grade_out_of_20`(decimal 4,2), `rank` | **unique**(`evaluation_entity_id`,`year`,`month`). العلاقة تستخدم `withTrashed()` |
| `daily_scripts` | `2026_09_14_000000_create_daily_scripts_table.php` | `content`(text nullable) | نص يومي يُعرض أعلى جدول التقييم |

---

## 3. المسارات / Routes

جميع المسارات داخل `Route::middleware(['auth'])->group(...)` أو بتبعية له.

### 3.1 التمامات والجهات

| المجموعة | المسارات |
|---|---|
| `can:tmam.view` | `entity-attendance-dashboard`, `entities`, `attendance-templates`, `daily-attendances/pending-reminders`, `daily-attendances`, `daily-attendances/{dailyAttendance}` |
| `can:tmam.create` | `entities/create`, `entities`(POST), `attendance-templates/create`, `attendance-templates`(POST) |
| `can:tmam.edit` | `entities/{entity}/edit`, `entities/{entity}`(PUT), `attendance-templates/{attendanceTemplate}/edit`, `attendance-templates/{attendanceTemplate}`(PUT) |
| `can:tmam.delete` | `entities/{entity}`(DELETE), `attendance-templates/{attendanceTemplate}`(DELETE) |
| `can:tmam.import` | `entities-import`(GET/POST) |
| `can:tmam.view` | `daily-attendance-entities/{dailyAttendanceEntity}/mark-done`, `mark-not-done`(PATCH) |

### 3.2 التقييمات (Livewire views)

داخل مجموعة `can:tmam.view` + prefix `evaluations` باسم `evaluations.`:

| الطريقة | URI | View | اسم المسار |
|---|---|---|---|
| GET | `evaluations/entities` | `evaluations.entities` | `evaluations.entities` |
| GET | `evaluations/daily` | `evaluations.daily` | `evaluations.daily` |
| GET | `evaluations/dashboard` | `evaluations.dashboard` | `evaluations.dashboard` |
| GET | `evaluations/completion` | `evaluations.completion` | `evaluations.completion` |

> ⚠️ **مهم:** التقييمات محمية بـ `tmam.view` على مستوى المسار، لكن صلاحيات `evaluations.*` تُفحص داخل مكونات Livewire. راجع §8.

---

## 4. منطق Controllers / Controller Logic

### 4.1 `EntityController` (83 سطر)

CRUD لـ `entities` + استيراد Excel. يستخدم `StoreEntityRequest`/`UpdateEntityRequest`/`ImportEntitiesRequest`.

### 4.2 `AttendanceTemplateController` (131 سطر)

- `store()`: ينشئ القالب + يربطه بالجهات بـ `sync()` + **يُنشئ الدورة الأولى** تلقائياً.
- `update()`: لو تغيّر قائمة الجهات → **يُنشئ دورة جديدة** ويعرض رسالة مختلفة. على التحديث، `is_active` افتراضياً `false` (مقابل `true` عند الإنشاء).
- `destroy()`: حذف بسيط مع cascade على الجداول المرتبطة.

### 4.3 `DailyAttendanceController` (65 سطر)

- `index()`: يعرض تمامات اليوم **بعد فلترة PHP** بناءً على وقت القالب + هامش 5 دقائق.
- `pendingReminders()`: JSON للتنبيهات، يستخدم نفس الوقت لكن **بدون هامش 5 دقائق**.
- ⚠️ **يستخدم `attendance_time` فقط** حتى لو القالب `twice_daily` وله `second_attendance_time`.

### 4.4 `DailyAttendanceEntityController` (79 سطر)

- `markDone()`/`markNotDone()`: تحديث الحالة + `response_at = now()` ثم استدعاء `syncParentStatus()`.
- `syncParentStatus()`: يحوّل `created` → `in_progress` عند أول رد، ويحوّل لـ `completed` عند انتهاء كل الصفوف `pending`.

### 4.5 `EntityAttendanceDashboardController` (78 سطر)

إحصائيات بالتاريخ (افتراضي آخر 30 يوم). **يحمل كل الجهات ويُجرى 3 استعلامات لكل جهة** (N+1) + `LengthAwarePaginator` يدوياً.

---

## 5. Livewire مكونات التقييمات

| المكون | الملف | الدور |
|---|---|---|
| `DailyEvaluationForm` | `app/Livewire/Evaluations/DailyEvaluationForm.php` | تقييم يومي بالجهات — منع التقييم يوم الجمعة، منع التقييم المكرر، حساب النقاط تلقائياً، تحرير "نص اليوم" |
| `EntityManager` | `app/Livewire/Evaluations/EntityManager.php` | إدارة جهات التقييم (CRUD + تفعيل/تعطيل + soft delete) |
| `Dashboard` | `app/Livewire/Evaluations/Dashboard.php` | لوحة التقييم الشهري + أعلى/أقل + ترتيب + إعادة الحساب |
| `MonthlyCompletion` | `app/Livewire/Evaluations/MonthlyCompletion.php` | متابعة نسبة الإنجاز اليومي للشهر (poll كل 120 ثانية) |

**نقاط حرجة:**
- النقاط تُحسب في `Evaluation::booted()` عبر hook `saving`: `score = SCORES[$response_type] ?? 0`
- لا يمكن تعديل أو حذف تقييم موجود (الواجهة تحوّل الأزرار لـ badge عند وجود تقييم)
- `MonthlyCompletion` يستخدم `abort_unless` يدوياً بدل `$this->authorize()`
- `Dashboard::recalculate()` يستدعي أمر Artisan مباشرة داخل التطبيق

---

## 6. الأوامر المجدولة / Commands & Scheduling

**`attendances:generate-daily`** (`app/Console/Commands/GenerateDailyAttendances.php`)
- يُشغّل يومياً `00:05` بتوقيت `Africa/Cairo` مع `withoutOverlapping()`
- يولد تمامات حسب القالب + التكرار + **دوران عشوائي** للجهات داخل الدورة
- عند استنفاد الدورة يُنشئ دورة جديدة

**`evaluations:calculate-monthly`** (`app/Console/Commands/CalculateMonthlyEvaluations.php`)
- مجدول يومياً `23:55` لكن **يُمرّر `now()->year/month` (الشهر الحالي)** مع تجاهل القيمة الافتراضية `subMonthNoOverflow()`
- يستثني **الجمعة** من عدد أيام العمل
- يحسب النسبة والدرجات من 20 والترتيب

---

## 7. الصلاحيات / Permissions

### تعريف الصلاحيات

**في `RolesAndPermissionsSeeder.php`:**
- `tmam.view`, `tmam.create`, `tmam.edit`, `tmam.delete`, `tmam.import`
- `موظف تمام` يحصل على `view/create/edit` فقط
- `مستخدم عادي` يحصل على `tmam.view` فقط

**في `EvaluationPermissionSeeder.php`:**
- `evaluations.evaluate`, `evaluations.manage`, `evaluations.dashboard`
- تُمنح فقط لـ `مشرف عام` تلقائياً

### التحقق

| المستوى | ملاحظات |
|---|---|
| **Routes** | `tmam.*` محمي على مستوى route لجميع مسارات التمامات. **التقييمات محمية بـ `tmam.view` فقط** على مستوى route |
| **Views** | `@can` على الأزرار والقوائم |
| **Livewire** | `authorize()`/`abort_unless()` داخل المكونات (لـ `evaluations.*`) |

---

## 8. قوالب التمام والدورات / Templates & Cycles

- **قالب واحد** يمكن أن يكون `twice_daily` وله `second_attendance_time` لكن **هذا الوقت غير مستخدم فعلياً** في توليد أو عرض أو تنبيهات التمامات
- **الدورة (Cycle)** تضمن عدم تكرار الجهة نفسها قبل إتمام كل جهات القالب
- **عند تعديل قائمة الجهات بالقالب** يُنشأ cycle جديد تلقائياً
- **التوزيع عشوائي** عبر `shuffle()->take()`
- **`frequency_config`** يدعم weekly/monthly/custom_dates لكن **فقط `daily`/`twice_daily` شائعة الاستخدام فعلياً**

---

## 9. واجهات المستخدم / Views Highlights

| المسار | الغرض |
|---|---|
| `resources/views/entities/*` | إدارة قائمة الجهات (التمامات) + استيراد Excel |
| `resources/views/attendance-templates/*` | إدارة قوالب التمام مع إعداد التكرار واختيار الجهات |
| `resources/views/daily-attendances/index.blade.php` | تمامات اليوم مع شريط التقدم |
| `resources/views/daily-attendances/show.blade.php` | تسجيل الأداء (AJAX) مع تحديث فوري |
| `resources/views/entity-attendance/index.blade.php` | إحصائيات التمامات بتاريخ مخصص + TOP5 + نسب |
| `resources/views/partials/tmam-reminder.blade.php` | منبه تلقائي كل 30 ثانية + صوت + تخزين محلي |
| `resources/views/evaluations/*` + `resources/views/livewire/evaluations/*` | واجهات التقييم كاملة |

---

## 10. المعالم المعروفة / Known Issues & Tech Debt

### حرجة / منطقية

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 1 | **`second_attendance_time` غير مستخدم** | `AttendanceTemplate`, `GenerateDailyAttendances`, `DailyAttendanceController`, Views | قوالب `twice_daily` تعمل لكن بتوقيت `attendance_time` فقط — UI misleading |
| 2 | **جدول التقييم الشهري مجدول بشكل خاطئ** | `routes/console.php:16-19` + `CalculateMonthlyEvaluations` | يحسب الشهر الحالي يومياً بدل الشهر السابق؛ قد يُحسب قبل اكتماله |
| 3 | **التقييمات محمية بـ `tmam.view`** لا بـ `evaluations.*` | `routes/web.php:208-222` | ربط غير منطقي بين موديولين مختلفين |
| 4 | **لا يمكن تعديل/حذف تقييم** | `DailyEvaluationForm` + Views | خطأ تقييم لا يُصلح إلا يدوياً |
| 5 | **حذف جهة التمام حرج** | `EntityController@destroy` (hard delete) | يحذفها مع سجلات الحضور المرتبطة |
| 6 | **`waiting` enum غير مستخدم** | `daily_attendances` migration | قيمة ميتة |

### أداء / تصميم

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 7 | **N+1 في لوحة إحصائيات التمامات** | `EntityAttendanceDashboardController:24-42` | بطيء مع عدد كبير من الجهات |
| 8 | **فلترة PHP بدل SQL** لعرض تمامات اليوم | `DailyAttendanceController@index` | صعب التوسيع |
| 9 | **`UpdateAttendanceTemplateRequest` نسخة طبق الأصل** | Request class | صيانة مزدوجة |
| 10 | **`AttendanceTemplateEntity` موديل فارغ تماماً** | `app/Models/AttendanceTemplateEntity.php` | كود ميت |
| 11 | **Model methods ميتة** | `DailyAttendanceEntity::markDone/NotDone` | غير مستخدمة |
| 12 | **Views مكررة/يتيمة** | `resources/views/evaluations/*` duplicates/orphans | فوضى ملفات |

### أخرى

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 13 | **Factories مفقودة** رغم استخدام `HasFactory` | عدة Models | صعوبة الاختبارات |
| 14 | **لا توجد اختبارات** لهذه الوحدات | `tests/` | بدون تغطية |
| 15 | **لا توثيق مخصص** في `docs/` | `docs/` | حتى الآن لا يوجد ملف لهذه الوحدات |
| 16 | **`EntityManager` يستخدم `updateOrCreate` بحذر** + `MonthlyCompletion` يستخدم `abort_unless` يدوياً | Livewire components | عدم اتساق مع باقي المكونات |

---

## 11. مسرد المصطلحات / Glossary

| في الكود | المعنى |
|---|---|
| `Entity` (attendance) | الجهة/القرية المشاركة في التمام |
| `AttendanceTemplate` | قالب التمام (وقت، عدد، جهات، تكرار) |
| `AttendanceCycle` | دورة التوزيع (لضمان عدم التكرار قبل الاكتمال) |
| `DailyAttendance` | جلسة تمام يومية (قد تكون للتمام الأول أو الثاني) |
| `DailyAttendanceEntity` | جهة ضمن جلسة التمام مع حالتها |
| `EvaluationEntity` | جهة خاضعة للتقييم |
| `Evaluation` | تقييم جهة في يوم محدد (نقاط تلقائية) |
| `MonthlyEvaluation` | ملخص شهري + ترتيب |
| `DailyScript` | نص يومي يُعرض في شاشة التقييم |
| `tmam.*` | صلاحيات وحدة التمامات/الجهات |
| `evaluations.*` | صلاحيات وحدة التقييمات |
| `frequency_config` | إعدادات التكرار الإضافية (أيام الأسبوع/تاريخ شهري/تواريخ مخصصة) |
| `time_slot` | رقم الوردية (1 أو 2) |
| `response_at` | وقت استجابة الجهة للتمام |
| `score` | النقاط المحسوبة تلقائياً حسب نوع الرد |
