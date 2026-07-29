<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Attachment_Tbl', function (Blueprint $table) {
            $table->increments('AttachmentID');
            $table->string('AttachmentName', 255)->nullable();
            $table->unsignedBigInteger('ReportID')->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->string('FileExtension', 10)->nullable();
            // $timestamps معطلة: الجدول الأصلي معندوش created_at/updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Attachment_Tbl');
    }
};