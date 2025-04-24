<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('world_country_in_special_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('world_countries')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('world_regions')->cascadeOnDelete();
            $table->date('start_date')->default(DB::raw('CURRENT_DATE'));
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('world_country_in_special_region');
    }
};
