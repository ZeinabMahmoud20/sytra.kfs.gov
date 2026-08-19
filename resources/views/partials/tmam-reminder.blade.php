@can('tmam.view')
    <div id="tmam-reminder-modal"
        class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-primary p-6 text-white flex items-center gap-3">
                <i class="fas fa-bell text-accent text-2xl animate-pulse"></i>
                <h3 class="text-xl font-black">حان موعد التمام</h3>
            </div>

            <div id="tmam-reminder-list" class="p-6 space-y-3 max-h-72 overflow-y-auto"></div>

            <div class="p-6 pt-0 flex gap-3">
                <button onclick="tmamCloseReminder()"
                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition-all">
                    تجاهل
                </button>
                <a href="{{ route('daily-attendances.index') }}"
                    class="flex-1 bg-primary hover:bg-primary/90 text-white font-bold py-3 rounded-xl text-center transition-all">
                    <i class="fas fa-arrow-left"></i> فتح تمامات اليوم
                </a>
            </div>
        </div>
    </div>

    <button id="tmam-reminder-bell" onclick="tmamOpenReminder()"
        class="hidden fixed bottom-6 left-6 z-[90] bg-red-600 hover:bg-red-700 text-white w-14 h-14 rounded-full shadow-2xl items-center justify-center transition-all animate-bounce">
        <i class="fas fa-bell text-xl"></i>
        <span id="tmam-reminder-count"
            class="absolute -top-1 -right-1 bg-white text-red-600 text-xs font-black w-5 h-5 rounded-full flex items-center justify-center"></span>
    </button>

    <script>
        const TMAM_REMINDERS_URL = "{{ route('daily-attendances.pending-reminders') }}";
        const TMAM_POLL_INTERVAL = 30000; // كل 30 ثانية
        let tmamCurrentDueItems = [];

        function tmamStorageKey() {
            const today = new Date().toISOString().slice(0, 10);
            return `tmam_dismissed_${today}`;
        }

        function tmamGetDismissed() {
            try {
                return JSON.parse(localStorage.getItem(tmamStorageKey())) || [];
            } catch (e) {
                return [];
            }
        }

        function tmamPlayBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = ctx.createOscillator();
                const gain = ctx.createGain();
                oscillator.connect(gain);
                gain.connect(ctx.destination);
                oscillator.type = 'sine';
                oscillator.frequency.value = 880;
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
                oscillator.start();
                oscillator.stop(ctx.currentTime + 0.6);
            } catch (e) {
                console.warn('تعذر تشغيل الصوت', e);
            }
        }

        function tmamRenderList(items) {
            const list = document.getElementById('tmam-reminder-list');
            list.innerHTML = items.map(item => `
                <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4">
                    <div>
                        <p class="font-bold text-primary">${item.template_name}</p>
                        <p class="text-xs text-slate-400">الموعد: ${item.time}</p>
                    </div>
                    <i class="fas fa-clock text-accent"></i>
                </div>
            `).join('');
        }

        function tmamOpenReminder() {
            document.getElementById('tmam-reminder-modal').classList.remove('hidden');
        }

        function tmamCloseReminder() {
            document.getElementById('tmam-reminder-modal').classList.add('hidden');

            const dismissed = tmamGetDismissed();
            const newDismissed = [...new Set([...dismissed, ...tmamCurrentDueItems.map(i => i.id)])];
            localStorage.setItem(tmamStorageKey(), JSON.stringify(newDismissed));

            tmamUpdateBell();
        }

        function tmamUpdateBell() {
            const dismissed = tmamGetDismissed();
            const stillPending = tmamCurrentDueItems.filter(i => !dismissed.includes(i.id));
            const bell = document.getElementById('tmam-reminder-bell');
            const count = document.getElementById('tmam-reminder-count');

            if (stillPending.length > 0) {
                bell.classList.remove('hidden');
                bell.classList.add('flex');
                count.textContent = stillPending.length;
            } else {
                bell.classList.add('hidden');
                bell.classList.remove('flex');
            }
        }

        async function tmamCheckReminders() {
            try {
                const response = await fetch(TMAM_REMINDERS_URL, {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) return;

                const items = await response.json();
                const dismissed = tmamGetDismissed();
                const newItems = items.filter(i => !dismissed.includes(i.id));

                const previousIds = tmamCurrentDueItems.map(i => i.id);
                const hasNewAlert = newItems.some(i => !previousIds.includes(i.id));

                tmamCurrentDueItems = items;

                if (newItems.length > 0) {
                    tmamRenderList(newItems);

                    if (hasNewAlert) {
                        tmamPlayBeep();
                        tmamOpenReminder();
                    }
                }

                tmamUpdateBell();
            } catch (e) {
                console.warn('فشل فحص التنبيهات', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            tmamCheckReminders();
            setInterval(tmamCheckReminders, TMAM_POLL_INTERVAL);
        });
    </script>
@endcan