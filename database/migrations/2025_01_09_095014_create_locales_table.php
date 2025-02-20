<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('name', 255);
            $table->string('native_name', 255);
            $table->string('system_locale', 255);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available_in_panel')->default(false);
            $table->string('datetime_format', 255);
            $table->string('date_format', 255);
            $table->string('time_format', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
