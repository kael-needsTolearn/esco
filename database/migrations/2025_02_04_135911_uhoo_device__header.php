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
        Schema::create('uhoo_Device_Header', function (Blueprint $table) {
            $table->string('Serial_Number',50)->primary();
            $table->string('Device_Name',100)->nullable();
            $table->string('Mac_Address',50)->nullable();
            $table->integer('Floor_Number')->nullable();
            $table->string('Room_Name',50)->nullable();
            $table->string('Time_Zone',50)->nullable();
            $table->string('UTC',50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uhoo_Device_Header');
    }
};
