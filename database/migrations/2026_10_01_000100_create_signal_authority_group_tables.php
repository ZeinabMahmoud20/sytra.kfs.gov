<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // مجموعة من جهات الإشارة، بيختارها المستخدم مرة واحدة بدل ما يختار كل جهة لوحدها
        Schema::create('SIGNAL_AUTHORITY_GROUP', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('GROUP_NAME', 100)->nullable();
            // $timestamps معطلة: باقي جداول الإعدادات معندهاش created_at/updated_at
        });

        // ربط جهات الإشارة بالمجموعات (عضوية متعدد لمتعدد)
        Schema::create('SIGNAL_AUTHORITY_GROUP_MEMBER', function (Blueprint $table) {
            $table->increments('ID');
            $table->unsignedInteger('GROUP_ID');
            $table->unsignedInteger('AUTHORITY_ID');

            $table->unique(['GROUP_ID', 'AUTHORITY_ID'], 'signal_auth_group_member_unique');
            $table->index('AUTHORITY_ID', 'signal_auth_group_member_authority_idx');

            $table->foreign('GROUP_ID')
                ->references('ID')->on('SIGNAL_AUTHORITY_GROUP')
                ->onDelete('cascade');

            $table->foreign('AUTHORITY_ID')
                ->references('ID')->on('SIGNAL_AUTHORITY')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SIGNAL_AUTHORITY_GROUP_MEMBER');
        Schema::dropIfExists('SIGNAL_AUTHORITY_GROUP');
    }
};
