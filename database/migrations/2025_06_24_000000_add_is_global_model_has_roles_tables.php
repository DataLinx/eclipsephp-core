<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->boolean('is_global')->default(false);
        });
    }

    public function down()
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
};
