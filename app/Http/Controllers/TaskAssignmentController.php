<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskAssignmentRequest;
use App\Http\Requests\UpdateTaskAssignmentRequest;
use App\Models\TaskEntity;
use App\Models\TaskAssignment;
use App\Models\TaskSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskAssignmentController extends Controller
{
    public function __construct()
    {
     //   $this->authorizeResource(TaskAssignment::class, 'task');
    }

    /**
     * الشاشة الرئيسية: جدول التكليفات + بحث بسيط + فلاتر متقدمة.
     * كل مستخدم يشوف نطاقه فقط حسب دوره (visibleTasksQuery في User).
     */
    public function index(Request $request)
    {
        $query = Auth::user()->visibleTasksQuery()
            ->with(['source', 'entity', 'assignee']);

        // بحث بسيط: رقم التكليف / المسؤول / الوصف
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('task_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('assignee', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // فلاتر متقدمة
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
              ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
              ->when($request->filled('entity_id'), fn ($q) => $q->where('entity_id', $request->entity_id))
              ->when($request->filled('from'), fn ($q) => $q->whereDate('received_date', '>=', $request->from))
              ->when($request->filled('to'), fn ($q) => $q->whereDate('received_date', '<=', $request->to))
              ->when($request->boolean('overdue_only'), function ($q) {
                  $q->where('status', '!=', 'تم التنفيذ')->whereDate('deadline', '<', now());
              });

        $tasks = $query->orderByDesc('received_date')->paginate(20)->withQueryString();

        return view('tasks.index', [
            'tasks'    => $tasks,
            'entities' => TaskEntity::active()->orderBy('name')->get(),
            'filters'  => $request->only(['q', 'status', 'priority', 'entity_id', 'from', 'to', 'overdue_only']),
        ]);
    }

    /** شاشة إضافة تكليف جديد وتخصيصه لموظف (زي إضافة بلاغ) */
    public function create()
    {
        return view('tasks.create', [
            'sources'        => TaskSource::active()->orderBy('name')->get(),
            'entities'       => TaskEntity::active()->orderBy('name')->get(),
            'staff'          => User::orderBy('name')->get(),
            'nextTaskNumber' => TaskAssignment::generateTaskNumber(),
        ]);
    }

    public function store(StoreTaskAssignmentRequest $request)
    {
        $task = DB::transaction(function () use ($request) {
            $documentPath = null;

            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('task-documents', 'public');
            }

            $task = TaskAssignment::create([
                'task_number'   => TaskAssignment::generateTaskNumber(),
                'document_type' => $request->document_type,
                'document_path' => $documentPath,
                'received_date' => $request->received_date,
                'source_id'     => $request->source_id,
                'entity_id'     => $request->entity_id,
                'assignee_id'   => $request->assignee_id,
                'created_by'    => Auth::id(),
                'description'   => $request->description,
                'priority'      => $request->priority,
                'deadline'      => $request->deadline,
                'status'        => 'لم يبدأ',
                'notes'         => $request->notes,
                'status_changed_at' => now(),
            ]);

            $task->logChange('status', null, 'لم يبدأ', Auth::id(), 'إنشاء تكليف جديد');

            return $task;
        });

        // تنبيه داخلي فوري للموظف المكلف
        $task->notifyAssignee();

        return redirect()->route('tasks.index')
            ->with('success', "تم تسجيل التكليف رقم {$task->task_number} وتخصيصه لـ {$task->assignee->name} بنجاح.");
    }

    /** شاشة تفاصيل التكليف + سجل كل التعديلات (audit trail) */
    public function show(TaskAssignment $task)
    {
        $task->load(['source', 'entity', 'assignee', 'creator', 'logs.changedBy']);

        return view('tasks.show', compact('task'));
    }

    /** شاشة التعديل الكامل - لصاحب التكليف الأصلي (created_by) أو admin فقط (الـ Policy بيتأكد) */
    public function edit(TaskAssignment $task)
    {
        return view('tasks.edit', [
            'task'     => $task,
            'sources'  => TaskSource::active()->orderBy('name')->get(),
            'entities' => TaskEntity::active()->orderBy('name')->get(),
            'staff'    => User::orderBy('name')->get(),
        ]);
    }

    /**
     * تحديث بيانات التكليف. الصلاحيات بتتحدد جوه UpdateTaskAssignmentRequest:
     * - مسؤول التكليفات/الإدارة: كل الحقول.
     * - الموظف صاحب التكليف: الحالة + النسبة + موعد الرد + الملاحظات بس.
     * كل تغيير بيتسجل في task_status_logs تلقائياً (audit trail) بدون
     * الحاجة لموافقة مسبقة - والمسؤول الإداري يشوفه في السجل بعدين.
     */
    public function update(UpdateTaskAssignmentRequest $request, TaskAssignment $task)
    {
        $original = $task->getOriginal();
        $changedBy = Auth::id();
        $editNote = $request->input('edit_note');
        $reassigned = $request->filled('assignee_id') && (int) $request->assignee_id !== $task->assignee_id;

        DB::transaction(function () use ($request, $task, $original, $changedBy, $editNote) {
            foreach ($request->validated() as $field => $value) {
                if ($field === 'edit_note') {
                    continue;
                }

                if (array_key_exists($field, $original) && (string) $original[$field] !== (string) $value) {
                    $task->logChange($field, $original[$field], $value, $changedBy, $editNote);
                }
            }

            $task->fill($request->validated());

            if ($task->isDirty('status')) {
                $task->status_changed_at = now();
            }

            $task->save();
        });

        if ($reassigned) {
            $task->notifyAssignee();
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'تم تحديث بيانات التكليف بنجاح.');
    }

    public function destroy(TaskAssignment $task)
    {
        if ($task->document_path) {
            Storage::disk('public')->delete($task->document_path);
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'تم حذف التكليف.');
    }
}