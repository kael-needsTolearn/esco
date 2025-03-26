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
        Schema::table('zoho_desks', function (Blueprint $table) {
            //
            $table->dropForeign(['Device_Id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zoho_desks', function (Blueprint $table) {
            //
            $table->foreign('Device_Id')->references('Device_Id')->on('devices');

        });
    }
};
