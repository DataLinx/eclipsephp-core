<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('world_regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('world_regions')->nullOnDelete();
            $table->boolean('is_special')->default(false);
            $table->softDeletes(); // For soft delete functionality
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('world_regions');
    }
};
