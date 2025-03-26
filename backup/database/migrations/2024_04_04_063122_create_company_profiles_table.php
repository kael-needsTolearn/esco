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
        Schema::create('company_profiles', function (Blueprint $table) {
            // $table->id();
            $table->string('Company_Id')->primary();
            // $table->string('Company_Id', 36)->primary()->default(DB::raw("CONCAT('ESCO-', UUID(), '-', NOW())"));
            $table->string('Company_Name');
            $table->string('Company_Address');
            $table->string('Country');
            $table->string('Account_Manager');
            $table->string('Account_Manager_Email');
            $table->string('Contract_Name');
            $table->string('Contract_Start_Date'); 
            $table->string('Contract_End_Date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
