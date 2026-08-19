@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'مستخدم';
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($currentUser?->name ?? 'User') . '&background=001f3f&color=fff';
@endphp

<header
    class="h-20 bg-white border-b border-slate-200 sticky top-0 z-30 px-6 sm:px-10 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-4">
        <button id="open-sidebar" class="lg:hidden text-2xl text-primary"><i class="fas fa-bars"></i></button>
        <h2 class="text-xl font-bold text-primary hidden sm:block">@yield('page-title', 'لوحة التحكم')</h2>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">
        <div class="relative group cursor-pointer">
            <div
                class="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-full transition-colors relative">
                <i class="far fa-bell text-xl"></i>
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            </div>
        </div>

        <div class="relative" id="user-dropdown-wrapper">
            <button id="user-dropdown-btn"
                class="flex items-center gap-3 pl-4 pr-2 py-2 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-200 group">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-sm font-bold text-primary leading-tight">{{ $currentUser?->name }}</span>
                    <span class="text-xs text-slate-400 font-medium">{{ $roleName }}</span>
                </div>
                <img src="{{ $avatarUrl }}" alt="User"
                    class="w-10 h-10 rounded-full border-2 border-accent shadow-md object-cover">
                <i
                    class="fas fa-chevron-down text-xs text-slate-400 group-hover:text-accent transition-colors hidden sm:inline"></i>
            </button>

            <div id="user-dropdown"
                class="absolute top-14 left-0 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden hidden z-50">
                <div class="p-4 bg-gradient-to-r from-primary to-[#003366] text-white flex items-center gap-3">
                    <img src="{{ $avatarUrl }}" class="w-12 h-12 rounded-full border-2 border-white/40 object-cover">
                    <div>
                        <p class="font-black text-sm">{{ $currentUser?->name }}</p>
                        <p class="text-xs text-white/70">{{ $roleName }}</p>
                    </div>
                </div>
                <div class="p-2">
                    <a href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-slate-700 font-bold">
                        <span
                            class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center text-primary"><i
                                class="fas fa-user-circle"></i></span>
                        الملف الشخصي
                    </a>
                    <a href="{{ Route::has('profile.settings') ? route('profile.settings') : '#' }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-slate-700 font-bold">
                        <span
                            class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center text-primary"><i
                                class="fas fa-cog"></i></span>
                        الإعدادات
                    </a>
                    <hr class="my-2 border-slate-100">
                    <button onclick="openLogoutModal()"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-50 transition-colors text-red-500 font-bold">
                        <span
                            class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-500"><i
                                class="fas fa-sign-out-alt"></i></span>
                        تسجيل الخروج
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
