<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * إزالة المسافات الزائدة من أول وآخر أسماء جهات البلاغ.
     *
     * الأعمدة دي من نوع TEXT، والبيانات كان فيها نفس الجهة متكتبة بأكثر من شكل
     * ('الكهرباء' و 'الكهرباء ' و 'الغاز  ' بمسافتين). و collation الجدول هو
     * utf8mb4_unicode_ci وهو PAD SPACE، يعني MySQL يعتبر النسخ دي متساوية في
     * المقارنة و DISTINCT، لكن Laravel بيقصّ المسافات من كل input عبر
     * TrimStrings، فكانت القيمة اللي المستخدم بيختارها بترفضها Rule::in.
     */
    public function up(): void
    {
        DB::table('REPORTING_TYPES')
            ->whereNotNull('AUTHORITY')
            ->whereRaw('AUTHORITY <> TRIM(AUTHORITY)')
            ->update(['AUTHORITY' => DB::raw('TRIM(AUTHORITY)')]);

        DB::table('RECIEVE_REPORT')
            ->whereNotNull('REPORTING_Auth')
            ->whereRaw('REPORTING_Auth <> TRIM(REPORTING_Auth)')
            ->update(['REPORTING_Auth' => DB::raw('TRIM(REPORTING_Auth)')]);
    }

    /**
     * العملية دي غير قابلة للتراجع لأن المسافات الزايدة بتتشال نهائياً.
     */
    public function down(): void
    {
        // مقصود: المسافات المقلمة مش ممكن ترجع بقيمتها الأصلية.
    }
};
