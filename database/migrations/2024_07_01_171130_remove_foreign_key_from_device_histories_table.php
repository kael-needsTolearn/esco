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
        Schema::table('device_histories', function (Blueprint $table) {
            //
            $table->dropForeign(['Device_ID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_histories', function (Blueprint $table) {
            //
            $table->foreign('Device_ID')->references('Device_Id')->on('devices');
        });
    }
};
