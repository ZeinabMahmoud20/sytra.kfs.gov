<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\AttendanceTemplateController;
use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\DailyAttendanceEntityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntityAttendanceDashboardController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SignalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Evaluations\DailyEvaluationForm;
use App\Livewire\Evaluations\Dashboard;
use App\Livewire\Evaluations\EntityManager;

Route::view('/', 'welcome');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth']);

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');


    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::get('/reports/report-types-by-auth', [ReportController::class, 'reportTypesByAuth'])
        ->name('reports.report-types-by-auth');
    Route::get('/reports/villages-by-city/{city}', [ReportController::class, 'villagesByCity'])
        ->name('reports.villages-by-city');

    Route::get('/reports/{report}/attachments/create', [ReportController::class, 'createAttachment'])
        ->name('reports.attachments.create');
    Route::post('/reports/{report}/attachments', [ReportController::class, 'storeAttachment'])
        ->name('reports.attachments.store');

    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Routes جديدة لجلب البيانات المرتبطة ديناميكياً (AJAX)
    Route::get('/reports/report-types-by-auth', [ReportController::class, 'reportTypesByAuth'])
        ->name('reports.report-types-by-auth');

    Route::get('/reports/villages-by-city/{city}', [ReportController::class, 'villagesByCity'])
        ->name('reports.villages-by-city');

    Route::get('/signals', [SignalController::class, 'index'])->name('signals.index');
    Route::get('/signals/create', [SignalController::class, 'create'])->name('signals.create');
    Route::post('/signals', [SignalController::class, 'store'])->name('signals.store');

    // لازم الـ export routes قبل route('signals.edit', {mainSignal}) عشان
    // Laravel ميحاولش يفسر "export" كـ ID
    Route::get('/signals/export/excel', [SignalController::class, 'exportExcel'])->name('signals.export.excel');
    Route::get('/signals/export/pdf', [SignalController::class, 'exportPdf'])->name('signals.export.pdf');

    Route::get('/signals/{mainSignal}/edit', [SignalController::class, 'edit'])->name('signals.edit');
    Route::put('/signals/{mainSignal}', [SignalController::class, 'update'])->name('signals.update');
    Route::delete('/signals/{mainSignal}', [SignalController::class, 'destroy'])->name('signals.destroy');
    Route::get('/signals/{mainSignal}', [SignalController::class, 'show'])
    ->middleware('permission:signals.view')->name('signals.show');



Route::middleware('can:tmam.view')->group(function () {
    Route::get('entity-attendance-dashboard', EntityAttendanceDashboardController::class)->name('entity-attendance-dashboard');

    Route::get('entities', [EntityController::class, 'index'])->name('entities.index');

    Route::get('attendance-templates', [AttendanceTemplateController::class, 'index'])->name('attendance-templates.index');

        Route::get('daily-attendances/pending-reminders',
        [DailyAttendanceController::class, 'pendingReminders'])
        ->name('daily-attendances.pending-reminders');


    Route::get('daily-attendances', [DailyAttendanceController::class, 'index'])->name('daily-attendances.index');
    
    Route::get('daily-attendances/{dailyAttendance}', [DailyAttendanceController::class, 'show'])->name('daily-attendances.show');
});

Route::middleware('can:tmam.create')->group(function () {
    Route::get('entities/create', [EntityController::class, 'create'])->name('entities.create');
    Route::post('entities', [EntityController::class, 'store'])->name('entities.store');

    Route::get('attendance-templates/create', [AttendanceTemplateController::class, 'create'])->name('attendance-templates.create');
    Route::post('attendance-templates', [AttendanceTemplateController::class, 'store'])->name('attendance-templates.store');
});

Route::middleware('can:tmam.edit')->group(function () {
    Route::get('entities/{entity}/edit', [EntityController::class, 'edit'])->name('entities.edit');
    Route::put('entities/{entity}', [EntityController::class, 'update'])->name('entities.update');

    Route::get('attendance-templates/{attendanceTemplate}/edit', [AttendanceTemplateController::class, 'edit'])->name('attendance-templates.edit');
    Route::put('attendance-templates/{attendanceTemplate}', [AttendanceTemplateController::class, 'update'])->name('attendance-templates.update');
});

Route::middleware('can:tmam.delete')->group(function () {
    Route::delete('entities/{entity}', [EntityController::class, 'destroy'])->name('entities.destroy');
    Route::delete('attendance-templates/{attendanceTemplate}', [AttendanceTemplateController::class, 'destroy'])->name('attendance-templates.destroy');
});

Route::middleware('can:tmam.import')->group(function () {
    Route::get('entities-import', [EntityController::class, 'showImportForm'])->name('entities.import.form');
    Route::post('entities-import', [EntityController::class, 'import'])->name('entities.import');
});

// متاحة لأي حد عنده tmam.view - أي حد شايف يقدر يسجل الرد
Route::middleware('can:tmam.view')->group(function () {
    Route::patch('daily-attendance-entities/{dailyAttendanceEntity}/mark-done', [DailyAttendanceEntityController::class, 'markDone'])
        ->name('daily-attendance-entities.mark-done');

    Route::patch('daily-attendance-entities/{dailyAttendanceEntity}/mark-not-done', [DailyAttendanceEntityController::class, 'markNotDone'])
        ->name('daily-attendance-entities.mark-not-done');


        Route::middleware(['auth'])->prefix('evaluations')->name('evaluations.')->group(function () {
    Route::view('/entities', 'evaluations.entities')->name('entities');
    Route::view('/daily', 'evaluations.daily')->name('daily');
    Route::view('/dashboard', 'evaluations.dashboard')->name('dashboard');
});
});











    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

        // إدارة الأدوار والصلاحيات (بما فيها الصلاحيات المباشرة) - محتاج roles.manage
        Route::middleware(['permission:roles.manage'])->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);

            Route::get('user-permissions', [UserPermissionController::class, 'index'])->name('user-permissions.index');
            Route::get('user-permissions/{user}/edit', [UserPermissionController::class, 'edit'])->name('user-permissions.edit');
            Route::put('user-permissions/{user}', [UserPermissionController::class, 'update'])->name('user-permissions.update');
        });


        
        // إدارة المستخدمين (إضافة/تعديل/حذف موظفين) - محتاج users.manage
        Route::middleware(['permission:users.manage'])->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });
    });
});


require __DIR__ . '/auth.php';
