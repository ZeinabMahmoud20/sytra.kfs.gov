<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceTemplateRequest;
use App\Http\Requests\UpdateAttendanceTemplateRequest;
use App\Models\AttendanceTemplate;
use App\Models\Entity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceTemplateController extends Controller
{
    public function index(): View
    {
        $templates = AttendanceTemplate::withCount('entities')
            ->orderBy('name')
            ->paginate(20);

        return view('attendance-templates.index', compact('templates'));
    }

    public function create(): View
    {
        $entities = Entity::orderBy('name')->get();

        return view('attendance-templates.create', compact('entities'));
    }

    public function store(StoreAttendanceTemplateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $template = AttendanceTemplate::create([
            'name' => $validated['name'],
            'attendance_time' => $validated['attendance_time'],
            'script' => $validated['script'],
            'daily_entities_count' => $validated['daily_entities_count'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $template->entities()->sync($validated['entity_ids']);

        // أول Cycle تلقائيًا لما ينشأ التمام
        $template->cycles()->create([
            'cycle_number' => 1,
        ]);

        return redirect()
            ->route('attendance-templates.index')
            ->with('success', 'تم إنشاء التمام بنجاح');
    }

    public function edit(AttendanceTemplate $attendanceTemplate): View
    {
        $entities = Entity::orderBy('name')->get();
        $selectedEntityIds = $attendanceTemplate->entities()->pluck('entities.id')->toArray();

        return view('attendance-templates.edit', [
            'template' => $attendanceTemplate,
            'entities' => $entities,
            'selectedEntityIds' => $selectedEntityIds,
        ]);
    }

public function update(UpdateAttendanceTemplateRequest $request, AttendanceTemplate $attendanceTemplate): RedirectResponse
{
    $validated = $request->validated();

    // نجيب الجهات القديمة قبل أي تعديل عشان نقارن بيها
    $oldEntityIds = $attendanceTemplate->entities()
        ->pluck('entities.id')
        ->sort()
        ->values()
        ->toArray();

    $newEntityIds = collect($validated['entity_ids'])
        ->map(fn ($id) => (int) $id)
        ->sort()
        ->values()
        ->toArray();

    $entitiesChanged = $oldEntityIds !== $newEntityIds;

    $attendanceTemplate->update([
        'name' => $validated['name'],
        'attendance_time' => $validated['attendance_time'],
        'script' => $validated['script'],
        'daily_entities_count' => $validated['daily_entities_count'],
        'is_active' => $validated['is_active'] ?? false,
    ]);

    $attendanceTemplate->entities()->sync($validated['entity_ids']);

    if ($entitiesChanged) {
        $lastCycleNumber = $attendanceTemplate->cycles()
            ->max('cycle_number');

        $attendanceTemplate->cycles()->create([
            'cycle_number' => $lastCycleNumber + 1,
        ]);
    }

    $message = $entitiesChanged
        ? 'تم تعديل التمام بنجاح، وتم بدء دورة (Cycle) جديدة بسبب تغيير الجهات'
        : 'تم تعديل التمام بنجاح';

    return redirect()
        ->route('attendance-templates.index')
        ->with('success', $message);
}

    public function destroy(AttendanceTemplate $attendanceTemplate): RedirectResponse
    {
        $attendanceTemplate->delete();

        return redirect()
            ->route('attendance-templates.index')
            ->with('success', 'تم حذف التمام بنجاح');
    }
}