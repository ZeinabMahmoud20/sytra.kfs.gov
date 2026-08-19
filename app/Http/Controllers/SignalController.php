<?php

namespace App\Http\Controllers;

use App\Exports\SignalsExport;
use App\Models\MainSignal;
use App\Models\SignalAuth;
use App\Models\SignalAuthority;
use App\Models\SignalContent;
use App\Models\SignalUnit;
use App\Models\SystemRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignalController extends Controller
{
    protected function validationRules(): array
    {
        return [
            'signals' => ['required', 'array', 'min:1'],
            'signals.*.date' => ['required', 'date'],
            'signals.*.time' => ['required'],
            'signals.*.sender' => ['required', 'exists:SIGNAL_AUTHORITY,ID'],
            'signals.*.content' => ['nullable', 'string'],
            'signals.*.subject' => ['nullable', 'string'],
            'signals.*.type' => ['required', 'in:إشارة لاسلكية,رصد مرئي'],
            'signals.*.authorities' => ['nullable', 'array'],
            'signals.*.authorities.*' => ['nullable', 'in:Correct,X'],
        ];
    }

    /**
     * قائمة الإشارات - صف واحد بس لكل ثريد (الإشارة الأساسية/الأولى)، مش كل رد لوحده.
     * لو عايز تشوف الردود، تدوس "عرض" وتتفتحلك كلها مع بعض.
     */
    public function index(Request $request)
    {
        // أول كارت (أقل ID) في كل ثريد = الإشارة الأساسية
        $firstUnitIds = SignalUnit::selectRaw('MIN(ID) as id')
            ->groupBy('MAIN_SEND_ID')
            ->pluck('id');

        $units = SignalUnit::query()
            ->join('MainSignalTBL', 'MainSignalTBL.MainSignalID', '=', 'SIGNAL_UNIT.MAIN_SEND_ID')
            ->join('users', 'users.id', '=', 'MainSignalTBL.RECEIVER_ID')
            ->whereIn('SIGNAL_UNIT.ID', $firstUnitIds)
            ->select(
                'SIGNAL_UNIT.*',
                'MainSignalTBL.MainSignalID',
                'MainSignalTBL.MainSignalCode',
                'users.name as receiver_name'
            )
            ->selectSub(function ($q) {
                // عدد كل الكروت في الثريد ده (بما فيها الإشارة الأساسية نفسها)
                $q->selectRaw('COUNT(*)')
                    ->from('SIGNAL_UNIT as su2')
                    ->whereColumn('su2.MAIN_SEND_ID', 'SIGNAL_UNIT.MAIN_SEND_ID');
            }, 'replies_count')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('UNIT_SIGNAL_DATE', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('UNIT_SIGNAL_DATE', '<=', $request->date_to))
            ->when(
                $request->filled('time_from') && $request->boolean('filter_by_time'),
                fn ($q) => $q->whereTime('UNIT_SIGNAL_TIME', '>=', $request->time_from)
            )
            ->when(
                $request->filled('time_to') && $request->boolean('filter_by_time'),
                fn ($q) => $q->whereTime('UNIT_SIGNAL_TIME', '<=', $request->time_to)
            )
            ->orderByDesc('UNIT_SIGNAL_DATE')
            ->orderByDesc('UNIT_SIGNAL_TIME')
            ->paginate(20)
            ->withQueryString();

        return view('signals.index', compact('units'));
    }

    /**
     * عرض الثريد كامل: الإشارة الأساسية + كل الردود عليها مرتبة زي محادثة.
     */
    public function show(MainSignal $mainSignal)
    {
        $units = SignalUnit::where('MAIN_SEND_ID', $mainSignal->MainSignalID)
            ->where('MAIN_SEND_CODE', $mainSignal->MainSignalCode)
            ->with('sender', 'authStates')
            ->orderBy('ID')
            ->get();

        $mainSignal->load('receiver');

        return view('signals.show', [
            'mainSignal' => $mainSignal,
            'units' => $units,
        ]);
    }

    public function create()
    {
        return view('signals.create', [
            'nextSignalCode' => $this->buildNextSignalCode(),
            'signalAuthorities' => SignalAuthority::orderBy('SIGNAL_NAME')->get(),
            'signalContents' => SignalContent::orderBy('SIGNALCONTENT')->pluck('SIGNALCONTENT'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), [
            'signals.*.sender.required' => 'اختر جهة إرسال الإشارة',
        ]);

        $mainSignal = DB::transaction(function () use ($validated) {
            $mainSignal = MainSignal::create([
                'MainSignalCode' => $this->buildNextSignalCode(),
                'RECEIVER_ID' => auth()->id(),
            ]);

            $this->saveUnits($validated['signals'], $mainSignal);

            return $mainSignal;
        });

        $this->logSystemRecord($request, 'إضافة بيانات إشارة جديدة', 'تمت إضافة بيانات إشارة جديدة في النظام بواسطة ');

        return redirect()
            ->route('signals.create')
            ->with('success', "تم حفظ بيانات الإشارة بنجاح برقم {$mainSignal->MainSignalCode}");
    }

    public function edit(MainSignal $mainSignal)
    {
        $existingUnits = SignalUnit::where('MAIN_SEND_ID', $mainSignal->MainSignalID)
            ->where('MAIN_SEND_CODE', $mainSignal->MainSignalCode)
            ->with('authStates')
            ->orderBy('ID')
            ->get()
            ->map(function ($unit) {
                return [
                    'date' => $unit->UNIT_SIGNAL_DATE,
                    'time' => substr($unit->UNIT_SIGNAL_TIME, 0, 5),
                    'sender' => $unit->UNIT_AUTHORITY_ID,
                    'content' => $unit->UNIT_SIGNAL_CONTENT,
                    'subject' => $unit->UNIT_SIGNAL_SUBJECT,
                    'type' => $unit->UNIT_SIGNAL_TYPE,
                    'authorities' => $unit->authStates->pluck('STATE', 'CONTACT_NAME'),
                ];
            });

        return view('signals.edit', [
            'mainSignal' => $mainSignal,
            'existingUnits' => $existingUnits,
            'signalAuthorities' => SignalAuthority::orderBy('SIGNAL_NAME')->get(),
            'signalContents' => SignalContent::orderBy('SIGNALCONTENT')->pluck('SIGNALCONTENT'),
        ]);
    }

    public function update(Request $request, MainSignal $mainSignal)
    {
        $validated = $request->validate($this->validationRules(), [
            'signals.*.sender.required' => 'اختر جهة إرسال الإشارة',
        ]);

        DB::transaction(function () use ($validated, $mainSignal) {
            $oldUnitIds = SignalUnit::where('MAIN_SEND_ID', $mainSignal->MainSignalID)
                ->where('MAIN_SEND_CODE', $mainSignal->MainSignalCode)
                ->pluck('ID');

            SignalAuth::where('MAIN_SIGNAL_ID', $mainSignal->MainSignalID)
                ->where('MAIN_SIGNAL_CODE', $mainSignal->MainSignalCode)
                ->delete();

            SignalUnit::whereIn('ID', $oldUnitIds)->delete();

            $this->saveUnits($validated['signals'], $mainSignal);
        });

        $this->logSystemRecord($request, 'تعديل بيانات إشارة', 'تم تعديل بيانات إشارة في النظام بواسطة ');

        return redirect()
            ->route('signals.edit', $mainSignal)
            ->with('success', 'تم تعديل بيانات الإشارة بنجاح');
    }

    public function destroy(MainSignal $mainSignal)
    {
        DB::transaction(function () use ($mainSignal) {
            SignalAuth::where('MAIN_SIGNAL_ID', $mainSignal->MainSignalID)
                ->where('MAIN_SIGNAL_CODE', $mainSignal->MainSignalCode)
                ->delete();

            SignalUnit::where('MAIN_SEND_ID', $mainSignal->MainSignalID)
                ->where('MAIN_SEND_CODE', $mainSignal->MainSignalCode)
                ->delete();

            $mainSignal->delete();
        });

        SystemRecord::create([
            'USER_FULL_NAME' => auth()->user()->name,
            'DEVICE_NAME' => substr(request()->userAgent() ?? 'غير معروف', 0, 255),
            'MACHINE_IP' => request()->ip(),
            'TITLE' => 'حذف اشارة',
            'DESCRIBTION' => 'تم حذف اشارة من النظام بواسطة ' . auth()->user()->name,
            'CREATED_DATE' => now()->format('Y-m-d'),
            'ISACTIVE' => '1',
            'USER_ID' => auth()->id(),
        ]);

        return redirect()->route('signals.index')->with('success', 'تم حذف الإشارة بنجاح');
    }

    protected function filteredUnitsQuery(Request $request)
    {
        $firstUnitIds = SignalUnit::selectRaw('MIN(ID) as id')->groupBy('MAIN_SEND_ID')->pluck('id');

        return SignalUnit::query()
            ->join('MainSignalTBL', 'MainSignalTBL.MainSignalID', '=', 'SIGNAL_UNIT.MAIN_SEND_ID')
            ->join('users', 'users.id', '=', 'MainSignalTBL.RECEIVER_ID')
            ->whereIn('SIGNAL_UNIT.ID', $firstUnitIds)
            ->select('SIGNAL_UNIT.*', 'MainSignalTBL.MainSignalCode', 'users.name as receiver_name')
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('UNIT_SIGNAL_DATE', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('UNIT_SIGNAL_DATE', '<=', $request->date_to))
            ->orderByDesc('UNIT_SIGNAL_DATE')
            ->orderByDesc('UNIT_SIGNAL_TIME');
    }

    public function exportExcel(Request $request)
    {
        $units = $this->filteredUnitsQuery($request)->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new SignalsExport($units), 'الإشارات.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $units = $this->filteredUnitsQuery($request)->get();

        $pdf = Pdf::loadView('signals.export-pdf', compact('units'))->setPaper('a4', 'landscape');

        return $pdf->download('الإشارات.pdf');
    }

    protected function saveUnits(array $signals, MainSignal $mainSignal): void
    {
        foreach ($signals as $signalData) {
            $unit = SignalUnit::create([
                'UNIT_SIGNAL_DATE' => $signalData['date'],
                'UNIT_SIGNAL_TIME' => $signalData['time'],
                'UNIT_SIGNAL_SUBJECT' => $signalData['subject'] ?? null,
                'UNIT_SIGNAL_CONTENT' => $signalData['content'] ?? null,
                'UNIT_AUTHORITY_ID' => $signalData['sender'],
                'MAIN_SEND_ID' => $mainSignal->MainSignalID,
                'MAIN_SEND_CODE' => $mainSignal->MainSignalCode,
                'UNIT_SIGNAL_TYPE' => $signalData['type'],
            ]);

            foreach ($signalData['authorities'] ?? [] as $authorityName => $state) {
                if (!empty($state)) {
                    SignalAuth::create([
                        'CONTACT_NAME' => $authorityName,
                        'STATE' => $state,
                        'SIGNAL_ID' => $unit->ID,
                        'MAIN_SIGNAL_ID' => $mainSignal->MainSignalID,
                        'MAIN_SIGNAL_CODE' => $mainSignal->MainSignalCode,
                    ]);
                }
            }
        }
    }

    protected function logSystemRecord(Request $request, string $title, string $descriptionPrefix): void
    {
        SystemRecord::create([
            'USER_FULL_NAME' => auth()->user()->name,
            'DEVICE_NAME' => substr($request->userAgent() ?? 'غير معروف', 0, 255),
            'MACHINE_IP' => $request->ip(),
            'TITLE' => $title,
            'DESCRIBTION' => $descriptionPrefix . auth()->user()->name,
            'CREATED_DATE' => now()->format('Y-m-d'),
            'ISACTIVE' => '1',
            'USER_ID' => auth()->id(),
        ]);
    }

    protected function buildNextSignalCode(): string
    {
        $lastCode = DB::table('MainSignalTBL')
            ->selectRaw("MAX(CAST(REPLACE(MainSignalCode, 'SIGNAL-', '') AS UNSIGNED)) as max_num")
            ->value('max_num');

        return 'SIGNAL-' . (((int) $lastCode) + 1);
    }
}