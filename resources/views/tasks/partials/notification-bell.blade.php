{{--
    ضيف الـ include ده في الـ layout الرئيسي عندكم جوه الـ header:
    @include('tasks.partials.notification-bell')
--}}
<div x-data="taskNotifications()" x-init="load()" class="relative">
    <button @click="open = !open; if(open) load()" class="relative p-2 rounded-full hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span x-show="unreadCount > 0" x-text="unreadCount"
              class="absolute -top-1 -left-1 bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center"></span>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
         class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50" dir="rtl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-800 text-sm">التنبيهات</span>
            <button @click="markAllRead()" class="text-xs text-blue-600 hover:underline">تحديد الكل كمقروء</button>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <template x-if="items.length === 0">
                <p class="text-center text-gray-400 text-sm py-6">لا توجد تنبيهات</p>
            </template>

            <template x-for="item in items" :key="item.id">
                <a :href="'/notifications/' + item.id + '/open'"
                   class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50"
                   :class="!item.read ? 'bg-blue-50/50' : ''">
                    <p class="text-sm font-medium text-gray-800" x-text="item.title"></p>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="item.task_number + ' — ' + item.description"></p>
                    <p class="text-[11px] text-gray-400 mt-1" x-text="item.created"></p>
                </a>
            </template>
        </div>
    </div>
</div>

{{-- محتاج Alpine.js متضاف في الـ layout (متوفر افتراضياً مع Laravel Breeze/Jetstream) --}}
<script>
function taskNotifications() {
    return {
        open: false,
        items: [],
        unreadCount: 0,
        async load() {
            const res = await fetch('/notifications/recent');
            const data = await res.json();
            this.items = data.notifications;
            this.unreadCount = data.unread_count;
        },
        async markAllRead() {
            await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            this.unreadCount = 0;
            this.items = this.items.map(i => ({ ...i, read: true }));
        }
    }
}
</script>
