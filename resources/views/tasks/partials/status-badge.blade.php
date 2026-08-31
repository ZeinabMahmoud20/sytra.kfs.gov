@php
    $colors = [
        'تم التنفيذ'   => 'bg-green-100 text-green-800 border-green-300',
        'جاري التنفيذ' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'متأخر'        => 'bg-orange-100 text-orange-800 border-orange-300',
        'لم يبدأ'      => 'bg-red-100 text-red-800 border-red-300',
        'متوقف'        => 'bg-gray-200 text-gray-700 border-gray-400',
    ];
    $icons = \App\Models\TaskAssignment::STATUS_ICONS;
@endphp

<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colors[$status] ?? '' }}">
    <span>{{ $icons[$status] ?? '' }}</span>
    <span>{{ $status }}</span>
</span>
