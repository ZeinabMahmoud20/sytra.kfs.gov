@php
    // كل رابط في القائمة: اسم الـ Route + الأيقونة + العنوان + الصلاحية المطلوبة لظهوره
    // permission = null معناه الرابط ده ظاهر لأي مستخدم مسجل دخول من غير شرط صلاحية
    $navLinks = [
        ['route' => 'dashboard', 'icon' => 'fa-th-large', 'label' => 'الصفحة الرئيسية', 'permission' => null],
        ['route' => 'reports.create', 'icon' => 'fa-plus-circle', 'label' => 'إضافة بلاغ', 'permission' => 'reports.create'],
        ['route' => 'signals.create', 'icon' => 'fa-paper-plane', 'label' => 'تسجيل إشارة', 'permission' => 'signals.create'],
        ['route' => 'tasks.index', 'icon' => 'fa-tasks', 'label' => 'قائمة التممات', 'permission' => 'tmam.view'],
        ['route' => 'signals.index', 'icon' => 'fa-broadcast-tower', 'label' => 'قائمة الإشارات', 'permission' => 'signals.view'],
        ['route' => 'reports.index', 'icon' => 'fa-file-alt', 'label' => 'التقارير', 'permission' => 'reports.view'],
        ['route' => 'profile.settings', 'icon' => 'fa-cog', 'label' => 'الإعدادات', 'permission' => null],
    ];
@endphp

<aside id="sidebar"
    class="fixed top-0 bottom-0 right-0 w-72 bg-primary text-white z-50 shadow-2xl overflow-y-auto
        transform translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="p-6 flex flex-col h-full">
        <div class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/imgs/watania.png') }}" alt="Logo"
                    class="w-10 h-10 bg-white p-1 rounded-full shadow-lg">
                <div class="flex flex-col">
                    <span class="text-xl font-bold tracking-tight whitespace-nowrap text-white">الشبكة الوطنية</span>
                    <span class="text-[10px] font-bold text-accent">مركز سيطرة</span>
                </div>
            </div>
            <button id="close-sidebar" class="lg:hidden text-white text-2xl"><i class="fas fa-times"></i></button>
        </div>

        <nav class="flex-1 space-y-2">
            @foreach ($navLinks as $link)
                @if (is_null($link['permission']) || auth()->user()?->can($link['permission']))
                    @php $isActive = Route::has($link['route']) && request()->routeIs($link['route']); @endphp
                    <a href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                        class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <i class="fas {{ $link['icon'] }} w-6 text-center
                            {{ $isActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                        <span class="font-medium text-lg">{{ $link['label'] }}</span>
                    </a>
                @endif
            @endforeach

            @can('tmam.view')
                @php
                    $tmamRoutes = ['daily-attendances.index', 'daily-attendances.show', 'attendance-templates.*', 'entities.*', 'entity-attendance-dashboard'];
                    $isTmamActive = request()->routeIs($tmamRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('tmam-submenu').classList.toggle('hidden'); this.querySelector('.tmam-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isTmamActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-clipboard-check w-6 text-center
                                {{ $isTmamActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">التمامات</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm tmam-chevron transition-transform duration-300 {{ $isTmamActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="tmam-submenu" class="{{ $isTmamActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        <a href="{{ route('daily-attendances.index') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                {{ request()->routeIs('daily-attendances.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                            <i class="fas fa-calendar-day w-5 text-center text-accent"></i>
                            تمامات اليوم
                        </a>

                        <a href="{{ route('attendance-templates.index') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                {{ request()->routeIs('attendance-templates.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                            <i class="fas fa-list-ul w-5 text-center text-accent"></i>
                            قائمة التمامات
                        </a>

                        <a href="{{ route('entities.index') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                {{ request()->routeIs('entities.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                            <i class="fas fa-building w-5 text-center text-accent"></i>
                            قائمة الجهات
                        </a>

                        <a href="{{ route('entity-attendance-dashboard') }}"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                {{ request()->routeIs('entity-attendance-dashboard') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                            <i class="fas fa-chart-bar w-5 text-center text-accent"></i>
                            إحصائيات الجهات
                        </a>
                    </div>
                </div>
            @endcan

            @canany(['evaluate-entities', 'manage-evaluation-entities', 'view-evaluation-dashboard'])
                @php
                    $evalRoutes = ['evaluations.entities', 'evaluations.daily', 'evaluations.dashboard'];
                    $isEvalActive = request()->routeIs($evalRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('eval-submenu').classList.toggle('hidden'); this.querySelector('.eval-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                    {{ $isEvalActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-star-half-alt w-6 text-center
                        {{ $isEvalActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">تقييم الجهات</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm eval-chevron transition-transform duration-300 {{ $isEvalActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="eval-submenu" class="{{ $isEvalActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('manage-evaluation-entities')
                            <a href="{{ route('evaluations.entities') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                            {{ request()->routeIs('evaluations.entities') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-building w-5 text-center text-accent"></i>
                                إدارة الجهات
                            </a>
                        @endcan

                        @can('evaluate-entities')
                            <a href="{{ route('evaluations.daily') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                            {{ request()->routeIs('evaluations.daily') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-calendar-check w-5 text-center text-accent"></i>
                                التقييم اليومي
                            </a>
                        @endcan

                        @can('view-evaluation-dashboard')
                            <a href="{{ route('evaluations.dashboard') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                            {{ request()->routeIs('evaluations.dashboard') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-chart-line w-5 text-center text-accent"></i>
                                لوحة تحكم التقييم
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            @can('users.manage')
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                        {{ request()->routeIs('admin.users.*') ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                    <i class="fas fa-users w-6 text-center {{ request()->routeIs('admin.users.*') ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                    <span class="font-medium text-lg">إدارة المستخدمين</span>
                </a>
            @endcan

            @can('roles.manage')
                <a href="{{ route('admin.roles.index') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                        {{ request()->routeIs('admin.roles.*') ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                    <i class="fas fa-user-shield w-6 text-center {{ request()->routeIs('admin.roles.*') ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                    <span class="font-medium text-lg">الأدوار والصلاحيات</span>
                </a>

                <a href="{{ route('admin.permissions.index') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                        {{ request()->routeIs('admin.permissions.*') ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                    <i class="fas fa-key w-6 text-center {{ request()->routeIs('admin.permissions.*') ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                    <span class="font-medium text-lg">إدارة الصلاحيات</span>
                </a>

                <a href="{{ route('admin.user-permissions.index') }}"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                        {{ request()->routeIs('admin.user-permissions.*') ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                    <i class="fas fa-user-lock w-6 text-center {{ request()->routeIs('admin.user-permissions.*') ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                    <span class="font-medium text-lg">صلاحيات مباشرة للموظفين</span>
                </a>
            @endcan
        </nav>

        <div class="mt-auto border-t border-white/10 pt-6">
            <button onclick="openLogoutModal()"
                class="w-full flex items-center gap-4 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all duration-300 font-bold group">
                <i class="fas fa-sign-out-alt w-6 text-center group-hover:-translate-x-1 transition-transform"></i>
                <span class="text-lg">تسجيل الخروج</span>
            </button>
        </div>
    </div>
</aside>