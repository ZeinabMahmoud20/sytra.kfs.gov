# وحدة التكليفات — Tasks Module

> توثيق تقني ثنائي اللغة (عربي + مصطلحات إنجليزية) موجّه للمطورين.
> آخر مراجعة ضد الكود: أكتوبر 2026 — اللوثج الرئيسي: `app/Http/Controllers/TaskAssignmentController.php` (204 سطر).
> الوثائق الشريكة: [وحدة البلاغات](reports-module.md) · [وحدة الإشارات](signals-module.md).

---

## 1. نظرة عامة / Overview

وحدة التكليفات تدير **تكليفات العمل الرسمية** (وارد/صادر) المُوزّعة على الموظفين: كل تكليف له مصدر، جهة، مُكلَّف (assignee)، أولوية، موعد نهائي، نسبة إنجاز، وحالة — مع **تتبع كامل للتغييرات** (`task_status_logs`) و**إشعار داخل النظام** للمُكلَّف.

الفارق الجوهري عن البلاغات/الإشارات:

| البُعد | التكليفات |
|---|---|
| الفكرة | تكليف فردي موزّع (assignment) وليس سجل حدث |
| التحكم في الوصول | **هجين**: صلاحيات Spatie للصفحات + **`users.role` + Policy + `visibleTasksQuery()`** للبيانات |
| التسلسل الهرمي | `manager_id` (مدير → مرؤوسين) + `entity_id` (مشرف → جهة) |
| التتبع | `task_status_logs` لكل حقل متغيّر + إشعارات database |

**مسار الكود الرئيسي:**

```
routes/web.php (257-303)                       → مسارات الموديول
app/Http/Controllers/
  TaskAssignmentController.php                 → CRUD التكليفات + المستند + التتبع (204)
  TaskAssignmentDashboardController.php        → لوحة المتابعة/KPIs (153)
  TaskNotificationController.php               → إشعارات داخل النظام (46)
  TaskEntityController.php                     → إعدادات الجهات (56)
  TaskSourceController.php                     → إعدادات المصادر (56)
app/Models/{TaskAssignment, TaskEntity, TaskSource, TaskStatusLog}.php
app/Policies/TaskAssignmentPolicy.php          → التخويل القائم على الأدوار
app/Notifications/NewTaskAssignedNotification.php
resources/views/tasks/* · task-entities/* · task-sources/*
```

---

## 2. مخطط قاعدة البيانات / Database Schema

### 2.1 جداول الموديول (كلها بـ `$timestamps`)

#### `task_assignments` — جدول التكليفات (الرئيسي)

Migration: `2026_08_24_000004_create_task_assignments_table.php` (+ تعديلات §2.2) — Model: `app/Models/TaskAssignment.php`

| العمود | النوع | ملاحظات |
|---|---|---|
| `id` | bigIncrements | PK |
| `task_number` | string, **unique** | `TA-{YEAR}-{00001}` — يُحسب بـ `generateTaskNumber()` |
| `document_type` | string(20), default `وارد` | `وارد` أو `صادر` |
| `document_path` | string, nullable | مسار الملف على disk الـ `public` تحت `task-documents/` |
| `received_date` | date | تاريخ الورود |
| `source_id` | FK → `task_sources.id` | مصدر التكليف |
| `entity_id` | FK → `task_entities.id` | الجهة المكلفة |
| `assignee_id` | FK → `users.id` | المُكلَّف |
| `created_by` | FK → `users.id` | المُسجِّل |
| `description` | text | الوصف — required max 5000 |
| `priority` | string(20), default `متوسطة` | `عالية`/`متوسطة`/`منخفضة` |
| `deadline` | date | الموعد النهائي |
| `response_date` | date, nullable | تاريخ الاستجابة |
| `completion_percentage` | unsignedTinyInteger, default 0 | نسبة الإنجاز |
| `status` | string(30), default `لم يبدأ` | 5 حالات (§3) |
| `notes` | text, nullable | max 2000 |
| `status_changed_at` | timestamp, nullable | آخر تغيير حالة |

**Indexes:** `status`, `assignee_id`, `entity_id`, `deadline`.

#### `task_entities` — الجهات (إعدادات)

Migration: `2026_08_24_000003_create_task_entities_table.php` — Model: `TaskEntity`

| العمود | النوع | ملاحظات |
|---|---|---|
| `id` | bigIncrements | PK |
| `name` | string, **unique** | |
| `type` | string(50), default `أخرى` | `مركز`/`مدينة`/`إدارة`/`مديرية`/`أخرى` |
| `is_active` | boolean, default true | |

يُزرع ابتدائيًا بـ **15 صفًا** (مراكز/مدن/إدارات). ⚠️ **منفصل تمامًا** عن جدول `entities` (موديول الحضور) — موثّق في تعليق الموديل.

#### `task_sources` — المصادر (إعدادات)

Migration: `2026_08_24_000002_create_task_sources_table.php` — Model: `TaskSource`

| العمود | النوع |
|---|---|
| `id` | bigIncrements PK |
| `name` | string **unique** |
| `is_active` | boolean default true |

يُزرع بـ **6 صفوف** (السيد المحافظ، نائب المحافظ، السكرتير العام، …).

#### `task_status_logs` — سجل التغييرات

Migration: `2026_08_24_000005_create_task_status_logs_table.php` — Model: `TaskStatusLog`

| العمود | النوع | ملاحظات |
|---|---|---|
| `id` | bigIncrements PK | |
| `task_assignment_id` | FK → `task_assignments.id` **cascadeOnDelete** | |
| `changed_by` | FK → `users.id` | |
| `field_name` | string | اسم الحقل المتغيّر |
| `old_value` / `new_value` | string, nullable | القيمة قبل/بعد |
| `note` | text, nullable | `edit_note` |

### 2.2 تعديلات لاحقة

| Migration | التغيير |
|---|---|
| `2026_08_24_000001_add_role_hierarchy_to_users_table.php` | يضيف `users.role` (enum: admin/director/manager/assignment_manager/supervisor/staff، افتراضي staff)، `users.manager_id` (self-FK `nullOnDelete`)، `users.entity_id` (unsignedBigInteger **بدون FK**) |
| `2026_08_31_000001_add_document_type_and_path_to_task_assignments_table.php` | يضيف `document_type` + `document_path` |
| `2026_08_24_000006_create_notifications_table.php` | جدول Laravel القياسي (uuid) — محمي بـ `Schema::hasTable()` |
| `2026_09_30_000000_fix_arabic_enum_columns.php` | يحوّل أعمدة `type/document_type/priority/status` العربية من `enum` إلى `VARCHAR` بـ `utf8mb4_unicode_ci` + يطبّع القيم غير الصحيحة |

> ⚠️ **لا توجد migration تنشئ صلاحيات `tasks.*`** — تُنشأ في الـ seeder فقط (خلافًا لـ `reports.monthly-stats`).

### 2.3 علاقات Model ER / Relationships

```
User ──(assignee_id)──► TaskAssignment ◄──(entity_id)── TaskEntity ── tasks() HasMany
  │  │   ▲                    │
  │  └───(created_by)─────────┤
  └──(manager_id) self-FK     │
                              ├──► TaskSource  عبر source_id
                              └──► TaskStatusLog (cascadeOnDelete)  ── changed_by ──► User
```

- `TaskAssignment`: `source()`, `entity()`, `assignee()`, `creator()`, `logs()` (HasMany `->latest()`).
- `User`: `assignedTasks()` (assignee_id)، `createdTasks()` (created_by).

---

## 3. نظام الأدوار والتخويل / Roles & Authorization

**مهم:** يوجد **نظامان منفصلان** لا يتحدثان مع بعضهما:

| النظام | المجال | المنطق |
|---|---|---|
| **Spatie** (أدوار عربية) | **الصفحات** | `tasks.view/create/edit/delete` — تظهر/تختفي روابط القائمة الجانبية والأزرار |
| **`users.role`** (enum إنجليزي) | **البيانات** | `TaskAssignmentPolicy` + `User::visibleTasksQuery()` — أي صفوف يُراها المستخدم |

### 3.1 الأدوار في `users.role` (المستوى الإنجليزي)

| الدور | نطاق الرؤية (`visibleTasksQuery()`) | حسب `TaskAssignmentPolicy` |
|---|---|---|
| `admin` | كل التكليفات | عرض/تعديل/حذف الجميع |
| `director` | كل التكليفات | عرض/تعديل الجميع، لا حذف |
| `assignment_manager` | كل التكليفات | عرض/تعديل/حذف الجميع |
| `manager` | نفسه + مرؤوسيه (`allSubordinateIds()`) | عرض/تعديل مهام فريقه |
| `supervisor` | تكليفات جهته (`entity_id`) | عرض تكليفات جهته |
| `staff` | نفسه فقط (`assignee_id`) | عرض/تعديل مهامه — **وإنشاء تكليفات أيضًا** (§11) |

### 3.2 `User::visibleTasksQuery()` (app/Models/User.php:122-132)

نواة التحكم في الرؤية — تُستدعى في `TaskAssignmentController@index` (`:31`) و`TaskAssignmentDashboardController@baseQuery` (`:23`). **`allSubordinateIds()`** (`:78-89`) يعمل BFS على `manager_id` لجمع المرؤوسين متسلسلًا.

### 3.3 `TaskAssignmentPolicy` (app/Policies/TaskAssignmentPolicy.php — 64 سطر)

Auto-discovered (Laravel 12: `App\Models\TaskAssignment` → `App\Policies\TaskAssignmentPolicy`).

| الدالة | الأسطر | المنطق |
|---|---|---|
| `viewAny()` | 11–14 | `return true` — **لا تُستدعى أبدًا** (لا `authorizeResource`) |
| `view()` | 16–32 | admin/director/assignment_manager → true؛ manager → نفسه أو مرؤوس؛ supervisor → نفس الجهة؛ وإلا المُكلَّف نفسه |
| `create()` | 35–38 | `in_array($role, ['admin','assignment_manager','director','staff'])` — ⚠️ `staff` مشمول رغم التعليق |
| `update()` | 44–52 | admin/assignment_manager/director → true؛ وإلا المُكلَّف |
| `updateStatus()` | 55–58 | يفوّض إلى `update()` |
| `delete()` | 60–63 | **admin أو assignment_manager فقط** |

**أين تُستخدم:**

| الموضع | الدالة | السطر |
|---|---|---|
| `StoreTaskAssignmentRequest::authorize()` | `can('create', TaskAssignment::class)` | `:11` |
| `UpdateTaskAssignmentRequest::authorize()` | `can('updateStatus', $task)` | `:13` |
| `UpdateTaskAssignmentRequest::rules()` | `can('update', $task)` لاختيار مجموعة القواعد | `:23` |
| `TaskAssignmentController::showDocument()` | `Gate::authorize('view', $task)` | `:179` |
| الواجهات | `@can('create'/'update'/'delete'/'updateStatus', ...)` | `tasks/index.blade.php:14,114,121`، `tasks/show.blade.php:138,148` |

> ⚠️ الـ Policy **لا تُستخدم** في `show()` و`edit()` و`destroy()` — انظر §11.

### 3.4 صلاحيات Spatie (المستوى العربي)

تُنشأ في `database/seeders/RolesAndPermissionsSeeder.php:41-44`، ويُمنحها **دور `مشرف عام` فقط** (`Permission::all()`). بقية الأدوار العربية لا تملك أي `tasks.*`.

---

## 4. المسارات / Routes

كلها في `routes/web.php` ضمن مجموعة `auth` (المفتوحة `:54`/`:257`).

### 4.1 التكليفات — `Route::resource` (web.php:257-264)

| الطريقة | URI | Action | الاسم | Middleware |
|---|---|---|---|---|
| GET | `tasks/dashboard` | `TaskAssignmentDashboardController@index` | `tasks.dashboard` | `auth` فقط |
| GET | `tasks` | `@index` | `tasks.index` | `auth` فقط |
| GET | `tasks/create` | `@create` | `tasks.create` | `auth` فقط |
| POST | `tasks` | `@store` | `tasks.store` | `auth` فقط (+ Policy في FormRequest) |
| GET | `tasks/{task}` | `@show` | `tasks.show` | `auth` فقط |
| GET | `tasks/{task}/edit` | `@edit` | `tasks.edit` | `auth` فقط |
| PUT/PATCH | `tasks/{task}` | `@update` | `tasks.update` | `auth` فقط (+ Policy في FormRequest) |
| DELETE | `tasks/{task}` | `@destroy` | `tasks.destroy` | **`auth` فقط — بلا تخويل** ⚠️ |
| GET | `tasks/{task}/document` | `@showDocument` | `tasks.document` | `auth` (+ `Gate::authorize('view')`) |

**الترتيب مهم:** `tasks/dashboard` مسجّل **قبل** الـ resource حتى لا يبتلعه `tasks/{task}` (تعليق `:259`).

### 4.2 الإشعارات (web.php:266-270 — بادئة `notifications`)

| الطريقة | URI | Action | الاسم |
|---|---|---|---|
| GET | `notifications/recent` | `@recent` | `task-notifications.recent` |
| GET | `notifications/{id}/open` | `@open` | `task-notifications.open` |
| POST | `notifications/mark-all-read` | `@markAllRead` | `task-notifications.mark-all-read` |

### 4.3 المصادر — محميّة بـ `can:` (web.php:272-286)

| الطريقة | URI | Middleware |
|---|---|---|
| GET | `task-sources` | `can:tasks.view` |
| GET/POST | `task-sources/create` / `task-sources` | `can:tasks.create` |
| GET/PUT | `task-sources/{taskSource}/edit` / `task-sources/{taskSource}` | `can:tasks.edit` |
| DELETE | `task-sources/{taskSource}` | `can:tasks.delete` |

### 4.4 الجهات — محميّة بـ `can:` (web.php:288-302)

نفس النمط تمامًا على `task-entities` مع `{taskEntity}`.

> **الفارق الحاسم:** مسارات الإعدادات محميّة فعليًا بـ `can:` على مستوى الـ route، بينما **مسارات التكليفات نفسها `auth` فقط** — الحماية الفعلية عبر Policy داخليًا (جزئيًا) و`@can` في الواجهة.

---

## 5. منطق Controllers / Controller Logic

### 5.1 `TaskAssignmentController` (204 سطر)

`__construct()` (`:19-22`): **`authorizeResource` مُعلَّق** — لا تخويل على مستوى الـ controller.

| الدالة | الأسطر | الملخص |
|---|---|---|
| `index(Request)` | 28–59 | `Auth::user()->visibleTasksQuery()->with(['source','entity','assignee'])`. بحث `q` عبر `task_number`/`description`/`assignee.name` (`:34-40`). فلاتر: `status`, `priority`, `entity_id`, `from`/`to` (على `received_date`), `overdue_only` (`status != 'تم التنفيذ' && deadline < now()`) (`:43-50`). ترتيب `received_date` تنازلي، paginate 20. يمرر الجهات النشطة + الفلاتر |
| `create()` | 62–70 | يمرر: `TaskSource::active()`، `TaskEntity::active()`، **`staff` = كل المستخدمين** (`User::orderBy('name')->get()` — بلا فلترة دور)، و`nextTaskNumber = generateTaskNumber()` |
| `store(StoreTaskAssignmentRequest)` | 72–108 | Transaction: حفظ الملف (`store('task-documents','public')`) → `create()` مع `status='لم يبدأ'`، `created_by=Auth::id()`، `status_changed_at=now()` → `logChange('status', null, 'إنشاء تكليف جديد')` → بعد commit `notifyAssignee()` → redirect |
| `show(TaskAssignment)` | 111–116 | يحمّل `source, entity, assignee, creator, logs.changedBy` → `tasks.show`. **بلا `Gate::authorize`** ⚠️ |
| `edit(TaskAssignment)` | 119–127 | يمرر المصادر/الجهات/الموظفين. **بلا تخويل** ⚠️ |
| `update(UpdateTaskAssignmentRequest, TaskAssignment)` | 136–169 | يلتقط `$original` ويحسب `$reassigned`. Transaction: **لكل حقل متغيّر** يستدعي `logChange(field, old, new, changedBy, editNote)` (`:144-152`) → `fill()` → إن تغيّرت الحالة `status_changed_at=now()` → `save()`. بعد commit: إعادة إشعار إن أُعيد الإسناد (`:163-165`) |
| `showDocument(TaskAssignment)` | 177–192 | **`Gate::authorize('view', $task)`** (`:179`) → 404 إن لا `document_path` أو الملف مفقود → `response(...)` مع `X-Content-Type-Options: nosniff` |
| `destroy(TaskAssignment)` | 194–203 | حذف الملف من disk ثم `delete()` (الحذف يتسلسل إلى السجلات عبر cascade). **بلا تخويل** ⚠️ |

> **لا يوجد تصدير Excel/PDF في هذه الوحدة.**

### 5.2 `TaskAssignmentDashboardController` (153 سطر)

- `const STATUSES = ['تم التنفيذ','جاري التنفيذ','متأخر','لم يبدأ','متوقف']` (`:18`).
- `baseQuery(Request)` (`:21-34`): `visibleTasksQuery()` + فلتر `from/to` على **`created_at`** (وليس `received_date`).
- `index()` (`:36-141`):
  - عدّ الحالات `groupBy('status')` (`:39-42`).
  - `$executionIndicators` (`:46-53`): total/completed/in_progress/overdue/not_started/halted.
  - `avgResponse` (`:57-61`): متوسط أيام الاستجابة — ⚠️ **خطأ إشارة** (§11).
  - `avgDelay` (`:64-66`): متوسط `delay_days` — يحمّل كل الصفوف في الذاكرة.
  - `commitmentRate` (`:69-77`): (منتهي && `response_date <= deadline`) ÷ **كل** التكليفات.
  - `avgCompletion` (`:80-83`): متوسط النسبة حيث `status != 'لم يبدأ'`.
  - كتلة الجهات (`:86-120`): `TaskEntity::active()->withCount([...6])` + `completion_rate` — ⚠️ **تتجاهل `visibleTasksQuery()`** (§11).
- `applyDateFilterToQuery()` (`:144-152`): **مكرر** مع منطق `baseQuery`.

### 5.3 `TaskNotificationController` (46 سطر)

| الدالة | الأسطر | الملخص |
|---|---|---|
| `recent()` | 11–29 | JSON: `unread_count` + آخر **10 إشعارات من أي نوع** للمستخدم (`id, read, title, task_number, description, url, created`). بلا فحص صلاحية/نوع |
| `open(string $id)` | 32–38 | `notifications()->findOrFail($id)` → `markAsRead()` → `redirect($data['url'] ?? tasks.index)` — ⚠️ **تغيير حالة عبر GET + open-redirect** (§11) |
| `markAllRead()` | 40–45 | `unreadNotifications->markAsRead()` → `{"ok":true}` |

### 5.4 `TaskEntityController` / `TaskSourceController` (56 سطر لكل)

CRUD بسيط عبر FormRequests، paginate 20، إعادة توجيه للفهرس مع رسائل عربية. لا منطق خاص.

---

## 6. قواعد التحقق من المدخلات / Validation

### 6.1 `StoreTaskAssignmentRequest` (`authorize` → `can('create')`)

| الحقل | القاعدة |
|---|---|
| `received_date` | required, date |
| `document_type` | required, `in:وارد,صادر` |
| `document` | nullable, file, `mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png`, **max:102400** (100MB) |
| `source_id` | required, `exists:task_sources,id` |
| `entity_id` | required, `exists:task_entities,id` |
| `assignee_id` | required, `exists:users,id` |
| `description` | required, string, max:5000 |
| `priority` | required, `in:عالية,متوسطة,منخفضة` |
| `deadline` | required, date, **`after_or_equal:received_date`** |
| `notes` | nullable, string, max:2000 |

### 6.2 `UpdateTaskAssignmentRequest` (`authorize` → `can('updateStatus', $task)`)

**مجموعتان حسب الصلاحية** (`:23`):

| الممثل | القواعد |
|---|---|
| المُكلَّف العادي (بلا `update`) | `status` (in:5 حالات)، `completion_percentage` (0–100)، `response_date`، `notes` — **حقوله فقط** |
| المُنشئ/admin/director/assignment_manager | كل الحقول **required** + `edit_note` nullable max:500 |

⚠️ **لا قاعدة `document`** → لا يمكن استبدال/حذف ملف بعد الإنشاء.

### 6.3 FormRequests الإعدادات (`authorize(): true`)

| الملف | القواعد |
|---|---|
| `Store/UpdateTaskEntityRequest` | `name` required\|max:255\|**unique** (`ignore` id عند التحديث)؛ `type` `in:مركز,مدينة,إدارة,مديرية,أخرى`؛ `is_active` sometimes\|boolean |
| `Store/UpdateTaskSourceRequest` | `name` required\|max:255\|**unique** (`ignore`)؛ `is_active` sometimes\|boolean |

---

## 7. الإشعارات / Notifications

### `NewTaskAssignedNotification` (app/Notifications/NewTaskAssignedNotification.php — 42 سطر)

- `use Queueable` لكن **ليس `ShouldQueue`** → يُرسل **متزامنًا**.
- `via()` (`:22-26`) → **`['database']` فقط** (لا بريد/SMS).
- `toArray()` (`:28-41`): `task_id`, `task_number`, `title` = "تكليف جديد مُخصص لك"، `description` (مقتطع 80)، `priority`, `deadline`, `entity`, `source`, `url` = `route('tasks.show', $task->id)`.
- **يُشغَّل** من `TaskAssignment::notifyAssignee()` (`:151-154`) — عند **الإنشاء** (`:104`) وعند **إعادة الإسناد** (`:164`). ⚠️ **لا يُشغَّل عند تغيير الحالة.**

> ⚠️ واجهة جرس الإشعارات (`resources/views/tasks/partials/notification-bell.blade.php`) **لا تُضمَّن في أي layout**؛ الـ header يعرض جرسًا ثابتًا غير فعّال بدلًا عنها (§11).

---

## 8. سجل التغييرات / Audit Trail

- يُكتب عبر `TaskAssignment::logChange()` (`app/Models/TaskAssignment.php:136-149`) — يُسجّل صف `TaskStatusLog` لكل حقل متغيّر (يتجاهل القيم المتساوية).
- التسميات العربية في `TaskStatusLog::FIELD_LABELS` (`:20-29`): status/priority/assignee_id/deadline/response_date/completion_percentage/entity_id/description.
- يُشغَّل من `TaskAssignmentController::update()` في حلقة `foreach` على الحقول (`:144-152`) ومرة عند الإنشاء (`:98`).
- `task_assignment_id` بـ `cascadeOnDelete` → السجلات تُحذف مع التكليف.
- ⚠️ **السجلات لا تُعرَض أبدًا**: `show()` تحمّلها (`:113`) لكن `tasks/show.blade.php` لا يشير إليها، و`FIELD_LABELS`/`getFieldLabelAttribute()` كود ميت (§11).

---

## 9. واجهات المستخدم / Views (Blade)

### 9.1 `resources/views/tasks/`

| الملف | الأسطر | الوظيفة |
|---|---|---|
| `index.blade.php` | 147 | القائمة + الفلاتر: زر "تكليف جديد" بـ `@can('create', TaskAssignment::class)` (`:14`)، نموذج فلاتر GET (`:29-68`: `q`, status, priority, entity, from/to, `overdue_only`)؛ الجدول (`:71-142`): تظليل الصف المتأخر حسب `is_overdue` (`:87`)، شريط الإنجاز (`:93-98`)، شارة الحالة (`:99`)، أيام التأخير، إجراءات show/`@can('update')`/`@can('delete')` |
| `create.blade.php` | 187 | نموذج إنشاء: معاينة رقم تلقائي (`:18`)، `enctype=multipart` (`:23`)، بيانات الورود (`:35-88`)، التخصيص والأولوية (`:91-131`، ملاحظة الإشعار `:106`)، الوصف/الملاحظات، flatpickr (`:165-187`). ⚠️ نص "حتى 10 ميجابايت" (`:86`) يناقض القاعدة 100MB |
| `edit.blade.php` | 235 | نموذج تعديل: PUT (`:31`)، رقم تكليف read-only، الحقول الكاملة، **`status` (`:130-135`)، `response_date` (`:124`)، `completion_percentage` (`:140`)، أيام التأخير (`:146`)**، رابط تنزيل المستند (`:169-178`)، **حقل `edit_note` (`:187`)** |
| `show.blade.php` | 200 | التفاصيل: شبكة بيانات الورود (`:32-66`)، شبكة الإسناد (`:69-108`: المُكلَّف، الأولوية، الموعد، نسبة الإنجاز، أيام التأخير)، الوصف/الملاحظات + المستند، زر تعديل `@can('update')` (`:138`)، ⚠️ **كتلة تغيير الحالة السريع `@can('updateStatus')` (`:148-187`) معطّلة فعليًا** — الـ select `disabled` والتحقق `readonly` |
| `dashboard.blade.php` | 273 | لوحة المتابعة: فلتر نطاق تاريخ (`:11-35`)، 6 بطاقات KPI تنفيذ (`:41-64`)، 4 بطاقات زمنية (`:70-115`)، رسمتا Chart.js (`:122,126`)، جدول مؤشرات الجهات (`:135-179`) |
| `partials/status-badge.blade.php` | 15 | شارة الحالة — `$colors` (`:2-8`) + `STATUS_ICONS` (`:9`) |
| `partials/notification-bell.blade.php` | 63 | Alpine bell — **لا تُضمَّن** (§11) |

### 9.2 `task-entities/` و`task-sources/` (3 ملفات لكل)

`index` (89/83) + `create` (66/55) + `edit` (65/55): جداول بأزرار محميّة `@can('tasks.*')`، شارات نشط/غير نشط، select `type` (للجهات فقط).

**التنقل** (`resources/views/partials/sidebar.blade.php:144-213`): قسم `@canany(['tasks.create','tasks.view'])` (`:145`) يحوي: تسجيل تكليف جديد (`@can('tasks.create')`)، قائمة التكليفات، لوحة المتابعة، مصادر التكليف، الجهات (`@can('tasks.view')`).

---

## 10. الحالات وسير العمل / Status Workflow

### 10.1 الحالات الخمس (نصوص عربية، بلا enum ولا قفل)

`لم يبدأ` (افتراضي) · `جاري التنفيذ` · `تم التنفيذ` · `متأخر` · `متوقف`

> **لا يوجد آلية قفل في هذا الموديول** (خلافًا للبلاغات). لا `locked_at`/`locked_by`.

### 10.2 الانتقالات

- **الإنشاء:** دائمًا `لم يبدأ` + `status_changed_at = now()`.
- **التعديل:** لا آلة حالة — يمكن القفز من `تم التنفيذ` إلى `لم يبدأ`. `status_changed_at` يُحدَّث عند التغيير فقط.
- **`متأخر` لا يُكتب آليًا أبدًا** — موجود كخيار يدوي ومفهوم مشتق (`is_overdue`)؛ لذا مؤشر `overdue` في اللوحة يكون عادة 0.

### 10.3 تدفق الإسناد (من يُكلّف مَن)

```
1. ممثل يملك Policy::create (admin/assignment_manager/director/staff)
   يفتح tasks/create ← قائمة المُكلَّفين = كل المستخدمين (بلا فلترة)
2. store(): created_by=Auth::id()، assignee_id=<المختار>، status='لم يبدأ'
3. notifyAssignee() → إشعار database للمُكلَّف
4. المُكلَّف يعدّل فقط (status, completion_percentage, response_date, notes)
   — عبر tasks/edit حصرًا (كتلة show معطّلة)
5. إعادة الإسناد → إعادة إشعار
6. الحذف بلا قيد (hard delete) → يمحو المستند والسجلات (cascade)
```

**الدوال المشتقة في `TaskAssignment`:** `getDelayDaysAttribute()` (`:85-102`)، `getStatusIconAttribute()` (`:104-107`)، `getIsOverdueAttribute()` (`:109-112`).

---

## 11. المعالم المعروفة / Known Issues & Tech Debt

### أمنية / حرجة

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 1 | **`destroy()` بلا تخويل** — والمسار بلا `can:` و`authorizeResource` معلّق | `TaskAssignmentController.php:194-203`؛ `routes/web.php:263` | **أي مستخدم مسجّل يستطيع حذف أي تكليف** + مستنده + سجلاته. الواجهة تخفي الزر فقط. الإصلاح: `$this->authorize('delete', $task)` |
| 2 | **`show()` بلا `Gate::authorize('view')`** | `:111-116` | IDOR — أي مستخدم يقرأ أي تكليف برقمه (رغم أن `showDocument()` يُخوّل) |
| 3 | **`edit()` بلا تخويل** | `:119-127` | كشف كل بيانات أي تكليف؛ الحفظ محجوب بالـ FormRequest فقط |
| 4 | **`tasks.index` و`tasks.dashboard` بلا `can:tasks.view`** | `routes/web.php:260-264` | أي مسجّل يصل مباشرة رغم أن القائمة الجانبية تخفيهم |
| 5 | **نظاما أدوار منفصلان** (Spatie عربي للصفحات، `users.role` إنجليزي للبيانات) | `RolesAndPermissionsSeeder` مقابل `TaskAssignmentPolicy`، `User::visibleTasksQuery()` | تعارض صلاحيات: من يملك `tasks.view` بـ Spatie لكن `role=staff` يرى مهامه فقط؛ والعكس |
| 6 | **المستندات على disk الـ `public`** | `:78` (`store('task-documents','public')`) | إن نُفّذ `storage:link` يصبح الملف متاحًا على `/storage/...` متجاوزًا `Gate::authorize` |
| 7 | **`open()` تغيير حالة عبر GET + open-redirect** | `TaskNotificationController.php:32-38` | إعادة توجيه لأي `data['url']` |

### أخطاء وظيفية

| # | المشكلة | الموقع | التأثير |
|---|---|---|---|
| 8 | **متوسط زمن الاستجابة دائمًا 0** — خطأ إشارة في `diffInDays` (Carbon 3 signed) | `TaskAssignmentDashboardController.php:61` | يجب عكس الترتيب: `received->diffInDays(response)` |
| 9 | **كتلة تغيير الحالة السريع معطّلة** — inputs `disabled`/`readonly` لا تُرسل | `tasks/show.blade.php:148-187` | المُكلَّف مجبر على `tasks/edit` |
| 10 | **`TaskModuleDemoSeeder` لا يفعل شيئًا** — `role` خارج `User::$fillable` | `database/seeders/TaskModuleDemoSeeder.php:26-30` | `update(['role'=>...])` يُسقط الحقل صامتًا |
| 11 | **`users.entity_id` غامض** — يُقارن بـ `task_entities.id` لكن بلا FK واسمه يوحي بجدول `entities` (الحضور) | `add_role_hierarchy...:50`، `TaskAssignmentPolicy.php:28`، `User.php:129` | فلتر المشرف يطابق صفوفًا خاطئة صامتًا |
| 12 | **مؤشرات الجهات تتجاهل نطاق الرؤية** | `TaskAssignmentDashboardController.php:86-111` | المدير يرى إحصاءات عامة في الجدول لكن KPIs خاصّة به → تناقض |
| 13 | **`متأخر` لا يُحفظ آليًا** | عام | مؤشر `overdue` وعمود "متأخر" شبه بلا فائدة |
| 14 | **`commitmentRate` مقامه كل التكليفات** لا المنتهية | `:77` | تسمية "نسبة الالتزام" مضلّلة |
| 15 | **تعارض حد الملف**: القاعدة 100MB والنص "10 ميجابايت" | `StoreTaskAssignmentRequest:19,36` مقابل `tasks/create.blade.php:86` | إرباك للمستخدم |
| 16 | **ثغرة سباق في أرقام التكليفات** — `generateTaskNumber()` يُستدعى مرتين بلا قفل | `TaskAssignment.php:118-130`، `TaskAssignmentController:68,82` | تساويان متزامنان → خرق `unique` |
| 17 | **أي `staff` يملك إنشاء تكليفات** رغم التعليق | `TaskAssignmentPolicy.php:37` | تضارب مع النية الموثّقة |
| 18 | **قائمة المُكلَّفين = كل المستخدمين** بلا فلترة | `TaskAssignmentController.php:67` | إسناد لأي أحد حتى غير ذي علاقة |
| 19 | **لا إدارة للمستند بعد الإنشاء** | `UpdateTaskAssignmentRequest` | لا استبدال/حذف |

### أداء

| # | المشكلة | الموقع |
|---|---|---|
| 20 | تحميل **كل** التكليفات في الذاكرة مرتين لحساب متوسطين | `TaskAssignmentDashboardController.php:60-66` |
| 21 | كتابة N+1 على `task_status_logs` (SELECT+INSERT لكل حقل متغيّر داخل transaction) | `TaskAssignmentController.php:144-152` |

### كود ميت / تكرار

| # | المشكلة | الموقع |
|---|---|---|
| 22 | `__construct()` بلا فعل (`authorizeResource` معلّق) | `TaskAssignmentController.php:19-22` |
| 23 | `TaskAssignmentPolicy::viewAny()` لا تُستدعى | `:11-14` |
| 24 | `FIELD_LABELS` + `getFieldLabelAttribute()` لا تُستخدمان | `TaskStatusLog.php:20-29,41-44` |
| 25 | `getStatusIconAttribute()` غير مستخدم | `TaskAssignment.php:104-107` |
| 26 | `show()` تحمّل `logs.changedBy` ولا رأي يستهلكها | `TaskAssignmentController.php:113` |
| 27 | **جرس الإشعارات لا يُضمَّن في أي layout** → الـ endpoints الثلاثة غير قابلة للوصول من الواجهة | `tasks/partials/notification-bell.blade.php` |
| 28 | `use App\Models\TaskAssignment` import غير مستخدم | `TaskAssignmentDashboardController.php:5` |
| 29 | `applyDateFilterToQuery()` مكرر مع `baseQuery()` | `:144-152` |
| 30 | قوائم الحالات/الأولويات مكررة في 7+ مواضع | §3 |
| 31 | `HasFactory` بلا Factory موجود | `TaskAssignment.php:14` |
| 32 | `TaskSource` بلا `HasFactory` وبلا علاقة `tasks()` العكسية | `TaskSource.php` |
| 33 | `User::assignedTasks()`/`createdTasks()`/`isAdmin()`/`isAssignmentManager()`/`isManager()` غير مستخدمة | `User.php:91-116` |
| 34 | `is_active` بلا boolean cast | `TaskEntity.php`/`TaskSource.php` |
| 35 | **لا اختبارات إطلاقًا** للموديول | `tests/` |

---

## 12. مسرد المصطلحات / Glossary

| في الكود | المعنى |
|---|---|
| `TaskAssignment` / `task_assignments` | التكليف |
| `task_number` | رقم التكليف `TA-{YEAR}-{00001}` |
| `document_type` | نوع الورود: `وارد` / `صادر` |
| `assignee_id` | الموظف المُكلَّف |
| `created_by` | من سجّل التكليف |
| `source_id` | مصدر التكليف (`task_sources`) |
| `entity_id` | الجهة (`task_entities`) |
| `response_date` | تاريخ الاستجابة |
| `completion_percentage` | نسبة الإنجاز (0–100) |
| `status_changed_at` | توقيت آخر تغيير حالة |
| `TaskStatusLog` / `task_status_logs` | سجل تغييرات الحقول |
| `newValue`/`oldValue` | القيمة قبل/بعد التغيير |
| `edit_note` | ملاحظة التعديل (تُسجَّل في `note`) |
| `visibleTasksQuery()` | نطاق الرؤية حسب الدور |
| `allSubordinateIds()` | مرؤوسو المستخدم (BFS على `manager_id`) |
| `is_overdue` | متأخر (مشتق: `delay_days > 0 && status != 'تم التنفيذ'`) |
| `delay_days` | أيام التأخير عن الموعد النهائي |

---

## 13. مقارنة الوحدات الثلاث / Cross-Module Contrast

| البُعد | البلاغات | الإشارات | التكليفات |
|---|---|---|---|
| الوثيقة | [reports-module.md](reports-module.md) | [signals-module.md](signals-module.md) | هذه الوثيقة |
| الجذر | بلاغ فردي | ثريد + كروت | تكليف موزّع |
| الحالة/القفل | 4 حالات + قفل إداري | لا يوجد — `Correct/X` | 5 حالات بلا قفل |
| التحكم في الوصول | `auth` + `@can` | `auth` + `@can` | **Spatie (صفحات) + Policy/`users.role` (بيانات)** |
| التسلسل الهرمي | — | — | `manager_id` + `entity_id` |
| التتبع | `SystemRecord` | `SystemRecord` | **`task_status_logs` لكل حقل** |
| الإشعار | — | — | **database notification** |
| الترقيم | `REP-{n}` | `SIGNAL-{n}` | `TA-{YEAR}-{n}` |
| التصدير | Excel + PDF | Excel + PDF | **لا يوجد** |
