<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotifiedAuthRequest;
use App\Http\Requests\UpdateNotifiedAuthRequest;
use App\Models\NotifiedAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotifiedAuthController extends Controller
{
    public function index(): View
    {
        $notifiedAuths = NotifiedAuth::orderBy('Notified_Auth')->paginate(20);

        return view('settings.notified-auths.index', compact('notifiedAuths'));
    }

    public function create(): View
    {
        return view('settings.notified-auths.create');
    }

    public function store(StoreNotifiedAuthRequest $request): RedirectResponse
    {
        NotifiedAuth::create($request->validated());

        return redirect()
            ->route('settings.notified-auths.index')
            ->with('success', 'تم إضافة جهة الإخطار بنجاح');
    }

    public function edit(NotifiedAuth $notifiedAuth): View
    {
        return view('settings.notified-auths.edit', compact('notifiedAuth'));
    }

    public function update(UpdateNotifiedAuthRequest $request, NotifiedAuth $notifiedAuth): RedirectResponse
    {
        $notifiedAuth->update($request->validated());

        return redirect()
            ->route('settings.notified-auths.index')
            ->with('success', 'تم تعديل جهة الإخطار بنجاح');
    }

    public function destroy(NotifiedAuth $notifiedAuth): RedirectResponse
    {
        $notifiedAuth->delete();

        return redirect()
            ->route('settings.notified-auths.index')
            ->with('success', 'تم حذف جهة الإخطار بنجاح');
    }
}