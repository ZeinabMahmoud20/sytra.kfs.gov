<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskSourceRequest;
use App\Http\Requests\UpdateTaskSourceRequest;
use App\Models\TaskSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskSourceController extends Controller
{
    public function index(): View
    {
        $sources = TaskSource::orderBy('name')->paginate(20);

        return view('task-sources.index', compact('sources'));
    }

    public function create(): View
    {
        return view('task-sources.create');
    }

    public function store(StoreTaskSourceRequest $request): RedirectResponse
    {
        TaskSource::create($request->validated());

        return redirect()
            ->route('task-sources.index')
            ->with('success', 'تم إضافة مصدر التكليف بنجاح');
    }

    public function edit(TaskSource $taskSource): View
    {
        return view('task-sources.edit', ['source' => $taskSource]);
    }

    public function update(UpdateTaskSourceRequest $request, TaskSource $taskSource): RedirectResponse
    {
        $taskSource->update($request->validated());

        return redirect()
            ->route('task-sources.index')
            ->with('success', 'تم تعديل مصدر التكليف بنجاح');
    }

    public function destroy(TaskSource $taskSource): RedirectResponse
    {
        $taskSource->delete();

        return redirect()
            ->route('task-sources.index')
            ->with('success', 'تم حذف مصدر التكليف بنجاح');
    }
}
