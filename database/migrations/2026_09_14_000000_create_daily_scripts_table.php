<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نصوص ثابتة تظهر يوميًا أعلى صفحة تقييم الجهات (تُعدَّل من زر التعديل)
     */
    public function up(): void
    {
        Schema::create('daily_scripts', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_scripts');
    }
};