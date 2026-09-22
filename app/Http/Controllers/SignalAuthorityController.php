<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignalAuthorityRequest;
use App\Http\Requests\UpdateSignalAuthorityRequest;
use App\Models\SignalAuthority;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignalAuthorityController extends Controller
{
    public function index(): View
    {
        $authorities = SignalAuthority::orderBy('SIGNAL_NAME')->paginate(20);

        return view('settings.signal-authorities.index', compact('authorities'));
    }

    public function create(): View
    {
        return view('settings.signal-authorities.create');
    }

    public function store(StoreSignalAuthorityRequest $request): RedirectResponse
    {
        SignalAuthority::create($request->validated());

        return redirect()
            ->route('settings.signal-authorities.index')
            ->with('success', 'تم إضافة جهة الإشارة بنجاح');
    }

    public function edit(SignalAuthority $signalAuthority): View
    {
        return view('settings.signal-authorities.edit', ['authority' => $signalAuthority]);
    }

    public function update(UpdateSignalAuthorityRequest $request, SignalAuthority $signalAuthority): RedirectResponse
    {
        $signalAuthority->update($request->validated());

        return redirect()
            ->route('settings.signal-authorities.index')
            ->with('success', 'تم تعديل جهة الإشارة بنجاح');
    }

    public function destroy(SignalAuthority $signalAuthority): RedirectResponse
    {
        $signalAuthority->delete();

        return redirect()
            ->route('settings.signal-authorities.index')
            ->with('success', 'تم حذف جهة الإشارة بنجاح');
    }
}