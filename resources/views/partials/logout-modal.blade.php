<div id="logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-sign-out-alt text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-800 mb-2">تسجيل الخروج</h3>
        <p class="text-slate-500 mb-7">هل أنت متأكد أنك تريد تسجيل الخروج من حسابك؟</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeLogoutModal()"
                class="flex-1 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold py-3 rounded-xl transition-all">
                إلغاء
            </button>

            {{-- فورم حقيقي بيعمل POST لـ route الخروج الافتراضي بتاع Laravel/Breeze --}}
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                    class="w-full bg-red-500 text-white hover:bg-red-600 font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </button>
            </form>
        </div>
    </div>
</div>
