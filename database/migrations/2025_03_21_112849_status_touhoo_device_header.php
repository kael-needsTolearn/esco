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
        Schema::table('uhoo_device_header', function (Blueprint $table) {
            $table->string('Status')->after('Mac_Address')->nullable(); // Example column

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uhoo_device_header', function (Blueprint $table) {
            //
        });
    }
};
