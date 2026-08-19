<div dir="rtl" class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">تقييم الجهات اليومي</h1>
        <input type="date" wire:model.live="date" class="border rounded-lg p-2">
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4">{{ session('error') }}</div>
    @endif

    @if ($isFriday)
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">  اليوم الجمعة لا يوجد تقييم  🙂</div>
    @else
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-right">الجهة</th>
                        <th class="p-3 text-right">التقييم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entities as $entity)
                        <tr class="border-t">
                            <td class="p-3">{{ $entity->name }}</td>
                            <td class="p-3">
                                @if (isset($responses[$entity->id]))
                                    <span class="px-3 py-1 bg-gray-100 rounded-full text-xs">
                                        تم التسجيل: {{ $responseTypes[$responses[$entity->id]] }}
                                    </span>
                                @else
                                    <div class="flex gap-2 flex-wrap">
                                        @foreach ($responseTypes as $key => $label)
                                            <button
                                                wire:click="save({{ $entity->id }}, '{{ $key }}')"
                                                class="px-3 py-1 rounded-lg text-xs border hover:bg-blue-50">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
