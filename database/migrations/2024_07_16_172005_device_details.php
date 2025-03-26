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
        Schema::create('Device_Details', function (Blueprint $table) {
            $table->id('Device_Details_Id');
            $table->string('Device_Id');
            $table->string('Remarks')->nullable();
            $table->timestamps();

             // Define the foreign key constraint
             $table->foreign('Device_Id')->references('Device_Id')->on('devices')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Device_Details');
    }
};
