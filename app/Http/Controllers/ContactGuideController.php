<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportContactGuidesRequest;
use App\Http\Requests\StoreContactGuideRequest;
use App\Http\Requests\UpdateContactGuideRequest;
use App\Imports\ContactGuidesImport;
use App\Models\ContactGuide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ContactGuideController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactGuide::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('department_name', 'like', "%{$search}%")
                    ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        $guides = $query->orderBy('department_name')->paginate(20)->withQueryString();

        $departments = ContactGuide::query()
            ->select('department_name')
            ->distinct()
            ->orderBy('department_name')
            ->pluck('department_name');

        return view('contact-guides.index', compact('guides', 'departments'));
    }

    public function create(): View
    {
        return view('contact-guides.create');
    }

    public function store(StoreContactGuideRequest $request): RedirectResponse
    {
        ContactGuide::create($request->validated());

        return redirect()
            ->route('contact-guides.index')
            ->with('success', 'تم إضافة سجل دليل الاتصال بنجاح');
    }

    public function edit(ContactGuide $contactGuide): View
    {
        return view('contact-guides.edit', compact('contactGuide'));
    }

    public function update(UpdateContactGuideRequest $request, ContactGuide $contactGuide): RedirectResponse
    {
        $contactGuide->update($request->validated());

        return redirect()
            ->route('contact-guides.index')
            ->with('success', 'تم تعديل السجل بنجاح');
    }

    public function destroy(ContactGuide $contactGuide): RedirectResponse
    {
        $contactGuide->delete();

        return redirect()
            ->route('contact-guides.index')
            ->with('success', 'تم حذف السجل بنجاح');
    }

    public function showImportForm(): View
    {
        return view('contact-guides.import');
    }

    public function import(ImportContactGuidesRequest $request): RedirectResponse
    {
        $import = new ContactGuidesImport();

        Excel::import($import, $request->file('file'));

        $failuresCount = count($import->failures());

        if ($failuresCount > 0) {
            return redirect()
                ->route('contact-guides.index')
                ->with('warning', "تم رفع دليل الاتصال مع تجاهل {$failuresCount} صف بسبب أخطاء في البيانات");
        }

        return redirect()
            ->route('contact-guides.index')
            ->with('success', 'تم رفع دليل الاتصال بنجاح: أُضيفت الإدارات الجديدة وتُحدّثت بيانات الإدارات الموجودة');
    }
}