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
        Schema::create('user_accesses', function (Blueprint $table) {
            $table->id('Access_Id');
            $table->unsignedBigInteger('User_Id');
            $table->foreign('User_Id')->references('id')->on('users');
            $table->string('Company_Id');
            $table->foreign('Company_Id')->references('Company_Id')->on('company_profiles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access');
    }
};
