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
        Schema::create('zoho_desks', function (Blueprint $table) {
            $table->string('Ticket_Id')->primary();
            $table->string('Ticket_Number');
            $table->string('Company_Id');
            $table->foreign('Company_Id')->references('Company_Id')->on('company_profiles');
            $table->string('Device_Id');
            $table->foreign('Device_Id')->references('Device_Id')->on('devices');
            $table->string('Subject');
            $table->string('Status');
            $table->string('Remarks');
            $table->string('Log_Last_Online');
            $table->string('Elapse_Time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_desks');
    }
};
