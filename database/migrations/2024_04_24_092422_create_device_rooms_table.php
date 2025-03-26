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
        Schema::create('device_rooms', function (Blueprint $table) {
            // $table->id();
            $table->string('DeviceRoomID')->primary();
            $table->string('DeviceRoomName');
            $table->string('DeviceRoomLocation');
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
        Schema::dropIfExists('device_rooms');
    }
};
