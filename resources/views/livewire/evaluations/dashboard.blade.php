<div dir="rtl" class="p-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h1 class="text-xl font-bold">داشبورد تقييم الجهات</h1>
        <div class="flex items-center gap-2">
            <select wire:model.live="month" class="border rounded-lg p-2">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="border rounded-lg p-2">
                @foreach (range(now()->year - 1, now()->year) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <button wire:click="recalculate" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                إعادة حساب الشهر
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif

    @if ($results->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">لا توجد نتائج محسوبة لهذا الشهر بعد.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-sm text-green-700">🏆 أعلى جهة</div>
                <div class="text-lg font-bold">{{ $top->entity->name }}</div>
                <div class="text-sm">{{ $top->percentage }}% — {{ $top->grade_out_of_20 }} / 20</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-sm text-red-700">🔻 أقل جهة</div>
                <div class="text-lg font-bold">{{ $bottom->entity->name }}</div>
                <div class="text-sm">{{ $bottom->percentage }}% — {{ $bottom->grade_out_of_20 }} / 20</div>
            </div>
        </div>

        <table class="w-full bg-white shadow rounded-lg overflow-hidden text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-right">الترتيب</th>
                    <th class="p-3 text-right">الجهة</th>
                    <th class="p-3 text-right">مجموع النقاط</th>
                    <th class="p-3 text-right">النسبة</th>
                    <th class="p-3 text-right">الدرجة من 20</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($results as $row)
                    <tr class="border-t">
                        <td class="p-3">{{ $row->rank }}</td>
                        <td class="p-3">{{ $row->entity->name }}</td>
                        <td class="p-3">{{ $row->total_score }} / {{ $row->max_possible_score }}</td>
                        <td class="p-3">{{ $row->percentage }}%</td>
                        <td class="p-3">{{ $row->grade_out_of_20 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>