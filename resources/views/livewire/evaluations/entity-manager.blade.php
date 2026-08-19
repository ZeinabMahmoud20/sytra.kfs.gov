<div dir="rtl" class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold">إدارة جهات التقييم</h1>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg">+ إضافة جهة</button>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">اسم الجهة *</label>
                    <input type="text" wire:model="name" class="w-full border rounded-lg p-2">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">كود الجهة (اختياري)</label>
                    <input type="text" wire:model="code" class="w-full border rounded-lg p-2">
                    @error('code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm mb-1">ملاحظات</label>
                    <textarea wire:model="notes" class="w-full border rounded-lg p-2"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="is_active">
                    <label for="is_active">جهة فعّالة</label>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">حفظ</button>
                    <button type="button" wire:click="$set('showForm', false)" class="bg-gray-200 px-4 py-2 rounded-lg">إلغاء</button>
                </div>
            </form>
        </div>
    @endif

    <table class="w-full bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-gray-100 text-sm">
            <tr>
                <th class="p-3 text-right">الاسم</th>
                <th class="p-3 text-right">الكود</th>
                <th class="p-3 text-right">الحالة</th>
                <th class="p-3 text-right">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entities as $entity)
                <tr class="border-t">
                    <td class="p-3">{{ $entity->name }}</td>
                    <td class="p-3">{{ $entity->code ?? '-' }}</td>
                    <td class="p-3">
                        <button wire:click="toggleActive({{ $entity->id }})"
                            class="px-2 py-1 rounded-full text-xs {{ $entity->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $entity->is_active ? 'فعّالة' : 'موقوفة' }}
                        </button>
                    </td>
                    <td class="p-3 flex gap-2">
                        <button wire:click="edit({{ $entity->id }})" class="text-blue-600">تعديل</button>
                        <button wire:click="delete({{ $entity->id }})" wire:confirm="متأكد من الحذف؟" class="text-red-600">حذف</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $entities->links() }}</div>
</div>
