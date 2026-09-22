<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportingTypeRequest;
use App\Http\Requests\UpdateReportingTypeRequest;
use App\Models\ReportingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportingTypeController extends Controller
{
    public function index(): View
    {
        $reportingTypes = ReportingType::orderBy('REPORT_SORT')->paginate(20);
        $authorities = ReportingType::whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY');

        return view('settings.reporting-types.index', compact('reportingTypes', 'authorities'));
    }

    public function create(): View
    {
        $authorities = ReportingType::whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY');

        return view('settings.reporting-types.create', compact('authorities'));
    }

    public function store(StoreReportingTypeRequest $request): RedirectResponse
    {
        ReportingType::create($request->validated());

        return redirect()
            ->route('settings.reporting-types.index')
            ->with('success', 'تم إضافة نوع البلاغ بنجاح');
    }

    public function edit(ReportingType $reportingType): View
    {
        $authorities = ReportingType::whereNotNull('AUTHORITY')->distinct()->pluck('AUTHORITY');

        return view('settings.reporting-types.edit', compact('reportingType', 'authorities'));
    }

    public function update(UpdateReportingTypeRequest $request, ReportingType $reportingType): RedirectResponse
    {
        $reportingType->update($request->validated());

        return redirect()
            ->route('settings.reporting-types.index')
            ->with('success', 'تم تعديل نوع البلاغ بنجاح');
    }

    public function destroy(ReportingType $reportingType): RedirectResponse
    {
        $reportingType->delete();

        return redirect()
            ->route('settings.reporting-types.index')
            ->with('success', 'تم حذف نوع البلاغ بنجاح');
    }
}