<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskEntityRequest;
use App\Http\Requests\UpdateTaskEntityRequest;
use App\Models\TaskEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskEntityController extends Controller
{
    public function index(): View
    {
        $entities = TaskEntity::orderBy('name')->paginate(20);

        return view('task-entities.index', compact('entities'));
    }

    public function create(): View
    {
        return view('task-entities.create');
    }

    public function store(StoreTaskEntityRequest $request): RedirectResponse
    {
        TaskEntity::create($request->validated());

        return redirect()
            ->route('task-entities.index')
            ->with('success', 'تم إضافة الجهة المختصة بنجاح');
    }

    public function edit(TaskEntity $taskEntity): View
    {
        return view('task-entities.edit', ['entity' => $taskEntity]);
    }

    public function update(UpdateTaskEntityRequest $request, TaskEntity $taskEntity): RedirectResponse
    {
        $taskEntity->update($request->validated());

        return redirect()
            ->route('task-entities.index')
            ->with('success', 'تم تعديل الجهة المختصة بنجاح');
    }

    public function destroy(TaskEntity $taskEntity): RedirectResponse
    {
        $taskEntity->delete();

        return redirect()
            ->route('task-entities.index')
            ->with('success', 'تم حذف الجهة المختصة بنجاح');
    }
}
