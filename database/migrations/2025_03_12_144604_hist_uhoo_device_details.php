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
        Schema::dropIfExists('hist_uhoo_device_details');
        Schema::create('hist_uhoo_device_details', function (Blueprint $table) {
            $table->string('Serial_Number');
            $table->string('Label',200)->nullable();
            $table->decimal('Value',50,2)->nullable();
            $table->string('Condition',50)->nullable();
            $table->string('Measurement',200)->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->integer('hr')->comment('Hour (0-23)');

        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
