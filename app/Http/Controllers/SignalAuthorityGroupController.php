<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignalAuthorityGroupRequest;
use App\Http\Requests\UpdateSignalAuthorityGroupRequest;
use App\Models\SignalAuthority;
use App\Models\SignalAuthorityGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignalAuthorityGroupController extends Controller
{
    public function index(): View
    {
        $groups = SignalAuthorityGroup::with('authorities')
            ->withCount('authorities')
            ->orderBy('GROUP_NAME')
            ->paginate(20);

        return view('settings.signal-authority-groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('settings.signal-authority-groups.create', [
            'authorities' => SignalAuthority::orderBy('SIGNAL_NAME')->get(),
            'selectedAuthorities' => [],
        ]);
    }

    public function store(StoreSignalAuthorityGroupRequest $request): RedirectResponse
    {
        $group = SignalAuthorityGroup::create(['GROUP_NAME' => $request->validated('GROUP_NAME')]);
        $group->authorities()->sync($request->validated('authorities') ?? []);

        return redirect()
            ->route('settings.signal-authority-groups.index')
            ->with('success', 'تم إضافة مجموعة جهات الإشارة بنجاح');
    }

    public function edit(SignalAuthorityGroup $signalAuthorityGroup): View
    {
        return view('settings.signal-authority-groups.edit', [
            'group' => $signalAuthorityGroup,
            'authorities' => SignalAuthority::orderBy('SIGNAL_NAME')->get(),
            'selectedAuthorities' => $signalAuthorityGroup->authorities()->pluck('SIGNAL_AUTHORITY.ID')->all(),
        ]);
    }

    public function update(UpdateSignalAuthorityGroupRequest $request, SignalAuthorityGroup $signalAuthorityGroup): RedirectResponse
    {
        $signalAuthorityGroup->update(['GROUP_NAME' => $request->validated('GROUP_NAME')]);
        $signalAuthorityGroup->authorities()->sync($request->validated('authorities') ?? []);

        return redirect()
            ->route('settings.signal-authority-groups.index')
            ->with('success', 'تم تعديل مجموعة جهات الإشارة بنجاح');
    }

    public function destroy(SignalAuthorityGroup $signalAuthorityGroup): RedirectResponse
    {
        $signalAuthorityGroup->delete();

        return redirect()
            ->route('settings.signal-authority-groups.index')
            ->with('success', 'تم حذف مجموعة جهات الإشارة بنجاح');
    }
}
