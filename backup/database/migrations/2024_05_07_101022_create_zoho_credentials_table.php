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
        Schema::create('zoho_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('clientID')->nullable();
            $table->string('clientSecret')->nullable();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('orgID')->default('680708905');
            $table->string('departmentID')->default('351081000001812222');
            $table->string('contactID')->default('351081000060434001');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_credentials');
    }
};
