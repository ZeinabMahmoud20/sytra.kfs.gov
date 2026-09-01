<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_guides', function (Blueprint $table) {
            $table->id();
            $table->string('department_name')->unique();
            $table->string('manager_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('landline_number')->nullable();
            $table->string('additional_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_guides');
    }
};