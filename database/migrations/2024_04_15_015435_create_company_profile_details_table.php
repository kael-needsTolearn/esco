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
        Schema::create('company_profile_details', function (Blueprint $table) {
            $table->id('Details_Id');
            $table->string('Company_Id');
            $table->unsignedBigInteger('Api_Id');
            $table->timestamps();

             // Define the foreign key constraint
             $table->foreign('Company_Id')->references('Company_Id')->on('company_profiles')->onDelete('cascade');
             $table->foreign('Api_Id')->references('Api_Id')->on('api_accounts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profile_details');
    }
};
