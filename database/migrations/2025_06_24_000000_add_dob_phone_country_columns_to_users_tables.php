<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)
                ->after('email')
                ->nullable();
            $table->string('country_id', 2)
                ->after('password')
                ->nullable();
            $table->date('date_of_birth')
                ->after('country_id')
                ->nullable();

            $table->index('phone_number');
            $table->index('country_id');
            $table->index('date_of_birth');

            $table->foreign('country_id')
                ->references('id')
                ->on('world_countries')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);

            $table->dropIndex(['phone_number']);
            $table->dropIndex(['country_id']);
            $table->dropIndex(['date_of_birth']);

            $table->dropColumn([
                'date_of_birth',
                'phone_number',
                'country_id',
            ]);
        });
    }
};
