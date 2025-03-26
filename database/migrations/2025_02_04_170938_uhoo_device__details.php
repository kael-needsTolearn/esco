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
        Schema::create('uhoo_Device_Details', function (Blueprint $table) {
            $table->string('Serial_Number');
            $table->string('Label',200)->nullable();
            $table->decimal('Value',50,2)->nullable();
            $table->decimal('Prev_Value',50,2)->nullable();
            $table->string('Condition',50)->nullable();
            $table->string('Measurement',200)->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->nullable();

            $table->foreign('Serial_Number')
            ->references('Serial_Number')
            ->on('uhoo_Device_Header')
            ->onDelete('cascade') 
            ->onUpdate('cascade');
        });
          
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uhoo_Device_Details');
    }
};
