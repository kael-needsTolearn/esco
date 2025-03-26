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
        Schema::create('uhoo_Sensor', function (Blueprint $table) {
            $table->id();
            $table->string('Label',200)->nullable();
            $table->decimal('Value_Start',50,2)->nullable();
            $table->decimal('Value_End',50,2)->nullable();
            $table->string('Condition',200)->nullable();
            $table->timestamp('Created_At');
            $table->timestamp('Updated_At')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uhoo_Sensor');
    }
};
