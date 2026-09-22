<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignalContentRequest;
use App\Http\Requests\UpdateSignalContentRequest;
use App\Models\SignalContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignalContentController extends Controller
{
    public function index(): View
    {
        $signalContents = SignalContent::orderBy('SIGNALCONTENT')->paginate(20);

        return view('settings.signal-contents.index', compact('signalContents'));
    }

    public function create(): View
    {
        return view('settings.signal-contents.create');
    }

    public function store(StoreSignalContentRequest $request): RedirectResponse
    {
        SignalContent::create($request->validated());

        return redirect()
            ->route('settings.signal-contents.index')
            ->with('success', 'تم إضافة مضمون الإشارة بنجاح');
    }

    public function edit(SignalContent $signalContent): View
    {
        return view('settings.signal-contents.edit', compact('signalContent'));
    }

    public function update(UpdateSignalContentRequest $request, SignalContent $signalContent): RedirectResponse
    {
        $signalContent->update($request->validated());

        return redirect()
            ->route('settings.signal-contents.index')
            ->with('success', 'تم تعديل مضمون الإشارة بنجاح');
    }

    public function destroy(SignalContent $signalContent): RedirectResponse
    {
        $signalContent->delete();

        return redirect()
            ->route('settings.signal-contents.index')
            ->with('success', 'تم حذف مضمون الإشارة بنجاح');
    }
}