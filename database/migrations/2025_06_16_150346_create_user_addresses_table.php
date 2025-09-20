<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('recipient', 100);
            $table->string('company_name', 100)
                ->nullable();
            $table->string('company_vat_id', 50)
                ->nullable();
            $table->json('street_address');
            $table->string('postal_code', 50);
            $table->string('city', 100);
            $table->json('type')->nullable();
            $table->string('country_id', 2);
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
