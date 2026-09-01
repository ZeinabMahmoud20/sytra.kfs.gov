@php
    // روابط بسيطة (بدون قائمة منسدلة) بتظهر بشكل مباشر
    // permission = null معناه الرابط ده ظاهر لأي مستخدم مسجل دخول من غير شرط صلاحية
    $simpleTopLinks = [
        ['route' => 'dashboard', 'icon' => 'fa-th-large', 'label' => 'الصفحة الرئيسية', 'permission' => null],
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

            {{-- 1) الصفحة الرئيسية --}}
            @foreach ($simpleTopLinks as $link)
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

            {{-- 2) البلاغات (قائمة منسدلة: إضافة بلاغ - تقارير البلاغات) --}}
            @canany(['reports.create', 'reports.view'])
                @php
                    $reportsRoutes = ['reports.create', 'reports.index'];
                    $isReportsActive = request()->routeIs($reportsRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('reports-submenu').classList.toggle('hidden'); this.querySelector('.reports-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isReportsActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-file-alt w-6 text-center
                                {{ $isReportsActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">البلاغات</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm reports-chevron transition-transform duration-300 {{ $isReportsActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="reports-submenu" class="{{ $isReportsActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('reports.create')
                            <a href="{{ route('reports.create') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('reports.create') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-plus-circle w-5 text-center text-accent"></i>
                                إضافة بلاغ
                            </a>
                        @endcan

                        @can('reports.view')
                            <a href="{{ route('reports.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('reports.index') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-file-alt w-5 text-center text-accent"></i>
                                تقارير البلاغات
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            {{-- 3) الإشارات (قائمة منسدلة: الإشارات - قائمة الإشارات) --}}
            @canany(['signals.create', 'signals.view'])
                @php
                    $signalsRoutes = ['signals.create', 'signals.index'];
                    $isSignalsActive = request()->routeIs($signalsRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('signals-submenu').classList.toggle('hidden'); this.querySelector('.signals-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isSignalsActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-broadcast-tower w-6 text-center
                                {{ $isSignalsActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">الإشارات</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm signals-chevron transition-transform duration-300 {{ $isSignalsActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="signals-submenu" class="{{ $isSignalsActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('signals.create')
                            <a href="{{ route('signals.create') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('signals.create') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-paper-plane w-5 text-center text-accent"></i>
                                تسجيل إشارة  
                            </a>
                        @endcan

                        @can('signals.view')
                            <a href="{{ route('signals.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('signals.index') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-broadcast-tower w-5 text-center text-accent"></i>
                                قائمة الإشارات
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            {{-- 4) متابعة التكليفات --}}
            @canany(['tasks.create', 'tasks.view'])
                @php
                    $tasksRoutes = [
                        'tasks.create', 'tasks.index', 'tasks.show', 'tasks.edit', 'tasks.dashboard',
                        'task-sources.*', 'task-entities.*',
                    ];
                    $isTasksActive = request()->routeIs($tasksRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('tasks-submenu').classList.toggle('hidden'); this.querySelector('.tasks-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isTasksActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-tasks w-6 text-center
                                {{ $isTasksActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">متابعة التكليفات</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm tasks-chevron transition-transform duration-300 {{ $isTasksActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="tasks-submenu" class="{{ $isTasksActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('tasks.create')
                            <a href="{{ route('tasks.create') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('tasks.create') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-plus-circle w-5 text-center text-accent"></i>
                                تسجيل تكليف جديد
                            </a>
                        @endcan

                        @can('tasks.view')
                            <a href="{{ route('tasks.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('tasks.index') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-list-check w-5 text-center text-accent"></i>
                                قائمة التكليفات
                            </a>
                        @endcan

                        @can('tasks.view')
                            <a href="{{ route('tasks.dashboard') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('tasks.dashboard') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-chart-pie w-5 text-center text-accent"></i>
                                لوحة المتابعة
                            </a>
                        @endcan

                        @can('tasks.view')
                            <a href="{{ route('task-sources.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('task-sources.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-share-nodes w-5 text-center text-accent"></i>
                                مصادر التكليف
                            </a>
                        @endcan

                        @can('tasks.view')
                            <a href="{{ route('task-entities.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('task-entities.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-building w-5 text-center text-accent"></i>
                                الجهات التعامل
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            {{-- 5) دليل الاتصال --}}
            @canany(['contact-guides.create', 'contact-guides.view', 'contact-guides.import'])
                @php
                    $contactGuidesRoutes = ['contact-guides.index', 'contact-guides.create', 'contact-guides.edit', 'contact-guides.import.*'];
                    $isContactGuidesActive = request()->routeIs($contactGuidesRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('contact-guides-submenu').classList.toggle('hidden'); this.querySelector('.contact-guides-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isContactGuidesActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-address-book w-6 text-center
                                {{ $isContactGuidesActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">دليل الاتصال</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm contact-guides-chevron transition-transform duration-300 {{ $isContactGuidesActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="contact-guides-submenu" class="{{ $isContactGuidesActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('contact-guides.create')
                            <a href="{{ route('contact-guides.create') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('contact-guides.create') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-plus-circle w-5 text-center text-accent"></i>
                                إضافة جهة اتصال
                            </a>
                        @endcan

                        @can('contact-guides.view')
                            <a href="{{ route('contact-guides.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('contact-guides.index') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-book w-5 text-center text-accent"></i>
                                دليل الاتصال
                            </a>
                        @endcan

                        @can('contact-guides.import')
                            <a href="{{ route('contact-guides.import.form') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('contact-guides.import.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-file-excel w-5 text-center text-accent"></i>
                                رفع من Excel
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            {{-- 6) التمامات - زي ما هي من غير تعديل --}}
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

            {{-- 7) تقييم الجهات - زي ما هي من غير تعديل --}}
            @canany(['evaluations.evaluate', 'evaluations.manage', 'evaluations.dashboard'])
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
                        @can('evaluations.manage')
                            <a href="{{ route('evaluations.entities') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                            {{ request()->routeIs('evaluations.entities') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-building w-5 text-center text-accent"></i>
                                إدارة الجهات
                            </a>
                        @endcan

                        @can('evaluations.evaluate')
                            <a href="{{ route('evaluations.daily') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                            {{ request()->routeIs('evaluations.daily') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-calendar-check w-5 text-center text-accent"></i>
                                التقييم اليومي
                            </a>
                        @endcan

                        @can('evaluations.dashboard')
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

            {{-- 8) إدارة المستخدمين (قائمة منسدلة تجمع: إدارة المستخدمين - الأدوار والصلاحيات - إدارة الصلاحيات - صلاحيات مباشرة للموظفين) --}}
            @canany(['users.manage', 'roles.manage'])
                @php
                    $usersMgmtRoutes = ['admin.users.*', 'admin.roles.*', 'admin.permissions.*', 'admin.user-permissions.*'];
                    $isUsersMgmtActive = request()->routeIs($usersMgmtRoutes);
                @endphp

                <div>
                    <button type="button" onclick="document.getElementById('users-mgmt-submenu').classList.toggle('hidden'); this.querySelector('.users-mgmt-chevron').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                            {{ $isUsersMgmtActive ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                        <span class="flex items-center gap-4">
                            <i class="fas fa-users w-6 text-center
                                {{ $isUsersMgmtActive ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                            <span class="font-medium text-lg">إدارة المستخدمين</span>
                        </span>
                        <i class="fas fa-chevron-down text-sm users-mgmt-chevron transition-transform duration-300 {{ $isUsersMgmtActive ? 'rotate-180' : '' }}"></i>
                    </button>

                    <div id="users-mgmt-submenu" class="{{ $isUsersMgmtActive ? '' : 'hidden' }} mt-1 mr-4 space-y-1 border-r-2 border-white/10 pr-4">
                        @can('users.manage')
                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('admin.users.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-users w-5 text-center text-accent"></i>
                                إدارة المستخدمين
                            </a>
                        @endcan

                        @can('roles.manage')
                            <a href="{{ route('admin.roles.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('admin.roles.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-user-shield w-5 text-center text-accent"></i>
                                الأدوار والصلاحيات
                            </a>

                            <a href="{{ route('admin.permissions.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('admin.permissions.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-key w-5 text-center text-accent"></i>
                                إدارة الصلاحيات
                            </a>

                            <a href="{{ route('admin.user-permissions.index') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-300 text-sm
                                    {{ request()->routeIs('admin.user-permissions.*') ? 'bg-white/15 text-white font-bold' : 'hover:bg-white/10 text-white/80' }}">
                                <i class="fas fa-user-lock w-5 text-center text-accent"></i>
                                صلاحيات مباشرة للموظفين
                            </a>
                        @endcan
                    </div>
                </div>
            @endcanany

            {{-- 9) الإعدادات --}}
            <a href="{{ Route::has('profile.settings') ? route('profile.settings') : '#' }}"
                class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-300 group
                    {{ request()->routeIs('profile.settings') ? 'bg-accent text-white shadow-lg' : 'hover:bg-white/10' }}">
                <i class="fas fa-cog w-6 text-center
                    {{ request()->routeIs('profile.settings') ? '' : 'text-accent group-hover:scale-110 transition-transform' }}"></i>
                <span class="font-medium text-lg">الإعدادات</span>
            </a>

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