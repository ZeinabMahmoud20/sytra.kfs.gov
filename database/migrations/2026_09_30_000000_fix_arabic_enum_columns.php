<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * إصلاح أعمدة الـ enum ذات القيم العربية في موديول التكليفات.
 *
 * المشكلة: الأعمدة دي كانت معرّفة كـ enum بقيم عربية، ودي بتتقص على السيرفر
 * وتطلع خطأ "Data truncated for column 'x'" لأن ترميز قاعدة البيانات على السيرفر
 * (أو تعريف الـ enum نفسه بعد الاستيراد) مش utf8mb4.
 *
 * الحل: نحوّلها لـ VARCHAR بترميز utf8mb4 صريح. القيم المسموحة معرّفة أصلاً
 * في الـ FormRequests وفي قوائم الـ blade، فمفيش أي فقدان للتحقق.
 */
return new class extends Migration
{
    /**
     * table => [column => [allowed values..., default]]
     */
    private const COLUMNS = [
        'task_entities' => [
            'type' => ['values' => ['مركز', 'مدينة', 'إدارة', 'مديرية', 'أخرى'], 'length' => 50, 'default' => 'أخرى'],
        ],
        'task_assignments' => [
            'document_type' => ['values' => ['وارد', 'صادر'], 'length' => 20, 'default' => 'وارد'],
            'priority' => ['values' => ['عالية', 'متوسطة', 'منخفضة'], 'length' => 20, 'default' => 'متوسطة'],
            'status' => ['values' => ['لم يبدأ', 'جاري التنفيذ', 'تم التنفيذ', 'متأخر', 'متوقف'], 'length' => 30, 'default' => 'لم يبدأ'],
        ],
    ];

    public function up(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column, $definition, $isMysql) {
                    $modified = $blueprint->string($column, $definition['length'])
                        ->default($definition['default'])
                        ->change();

                    if ($isMysql) {
                        $modified->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                    }
                });

                DB::table($table)
                    ->where(function ($query) use ($column, $definition) {
                        $query->whereNull($column)
                            ->orWhere($column, '')
                            ->orWhereNotIn($column, $definition['values']);
                    })
                    ->update([$column => $definition['default']]);
            }
        }
    }

    public function down(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column, $definition, $isMysql) {
                    $modified = $blueprint->enum($column, $definition['values'])
                        ->default($definition['default'])
                        ->change();

                    if ($isMysql) {
                        $modified->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                    }
                });
            }
        }
    }
};
