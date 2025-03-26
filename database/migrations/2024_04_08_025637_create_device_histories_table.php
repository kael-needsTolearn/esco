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
        Schema::create('device_histories', function (Blueprint $table) {
            $table->id('Hist_Id');
            $table->string('Device_ID');
            $table->string('Device_Name');
            $table->string('Device_Desc');
            $table->string('Device_Loc');
            $table->string('Room_Type');
            $table->string('Manufacturer');
            $table->string('Serial_Num');
            $table->string('MAC_Add');
            $table->foreign('Device_ID')->references('Device_Id')->on('devices');
            $table->string('Status');
            $table->dateTime('Previous_Date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_histories');
    }
};
