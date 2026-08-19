<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportEntitiesRequest;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Imports\EntitiesImport;
use App\Models\Entity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EntityController extends Controller
{
    public function index(): View
    {
        $entities = Entity::orderBy('name')->paginate(20);

        return view('entities.index', compact('entities'));
    }

    public function create(): View
    {
        return view('entities.create');
    }

    public function store(StoreEntityRequest $request): RedirectResponse
    {
        Entity::create($request->validated());

        return redirect()
            ->route('entities.index')
            ->with('success', 'تم إضافة الجهة بنجاح');
    }

    public function edit(Entity $entity): View
    {
        return view('entities.edit', compact('entity'));
    }

    public function update(UpdateEntityRequest $request, Entity $entity): RedirectResponse
    {
        $entity->update($request->validated());

        return redirect()
            ->route('entities.index')
            ->with('success', 'تم تعديل الجهة بنجاح');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        $entity->delete();

        return redirect()
            ->route('entities.index')
            ->with('success', 'تم حذف الجهة بنجاح');
    }

    public function showImportForm(): View
    {
        return view('entities.import');
    }

    public function import(ImportEntitiesRequest $request): RedirectResponse
    {
        $import = new EntitiesImport();

        Excel::import($import, $request->file('file'));

        $failuresCount = count($import->failures());

        if ($failuresCount > 0) {
            return redirect()
                ->route('entities.index')
                ->with('warning', "تم الرفع مع تجاهل {$failuresCount} صف بسبب أخطاء في البيانات");
        }

        return redirect()
            ->route('entities.index')
            ->with('success', 'تم رفع الجهات بنجاح');
    }
}