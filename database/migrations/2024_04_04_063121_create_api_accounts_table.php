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
        Schema::create('api_accounts', function (Blueprint $table) {
            $table->id('Api_Id');
            $table->string('Platform');
            $table->string('Description');
            $table->string('Variable1',1000)->nullable();
            $table->string('Variable2',1000)->nullable();
            $table->string('Variable3',1000)->nullable();
            $table->string('Variable4',1000)->nullable();
            $table->string('Variable5',1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_accounts');
    }
};
