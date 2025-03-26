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
        Schema::create('devices', function (Blueprint $table) {
            // $table->id();
            $table->string('Device_Id', 255)->primary();
            $table->string('Device_Name'); 
            $table->string('DeviceRoomID'); 
            $table->string('Device_Desc');
            $table->string('Device_Loc');
            $table->string('Room_Type');
            $table->string('Manufacturer');
            $table->string('Serial_Number');
            $table->string('Mac_Address')->nullable();
            $table->string('Status');
            $table->unsignedBigInteger('Api_Id');
            $table->foreign('Api_Id')->references('Api_Id')->on('api_accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
