<?php

if (! function_exists('permission_label')) {
    /**
     * يترجم اسم صلاحية زي "reports.approve" إلى "البلاغات - اعتماد"
     * ولو مفيش ترجمة معرّفة، بيرجع الاسم الأصلي زي ما هو.
     */
    function permission_label(string $name): string
    {
        $parts = explode('.', $name, 2);
        $module = $parts[0] ?? $name;
        $action = $parts[1] ?? null;

        $modules = config('permission_translations.modules', []);
        $actions = config('permission_translations.actions', []);

        $moduleLabel = $modules[$module] ?? $module;
        $actionLabel = $action ? ($actions[$action] ?? $action) : null;

        return $actionLabel ? "{$moduleLabel} - {$actionLabel}" : $moduleLabel;
    }
}

if (! function_exists('home_route_for')) {
    /**
     * أول صفحة مسموح للمستخدم الحالي يدخلها بعد تسجيل الدخول.
     * بنستخدمها عشان لو المستخدم مش عنده صلاحية "dashboard.view"
     * مايتحولش على صفحة لوحة التحكم ويقابل 403.
     */
    function home_route_for(?\App\Models\User $user = null): string
    {
        $user ??= auth()->user();

        $candidates = [
            'dashboard' => 'dashboard.view',
            'reports.index' => 'reports.view',
            'signals.index' => 'signals.view',
            'daily-attendances.index' => 'tmam.view',
            'entity-attendance-dashboard' => 'tmam.statistics',
            'tasks.index' => 'tasks.view',
            'contact-guides.index' => 'contact-guides.view',
        ];

        foreach ($candidates as $routeName => $permission) {
            if (\Illuminate\Support\Facades\Route::has($routeName) && $user?->can($permission)) {
                return route($routeName);
            }
        }

        return route('profile');
    }
}

if (! function_exists('module_label')) {
    /**
     * يترجم اسم الـ module نفسه (مفتاح التجميع) لعربي
     */
    function module_label(string $module): string
    {
        return config('permission_translations.modules.' . $module, $module);
    }
}