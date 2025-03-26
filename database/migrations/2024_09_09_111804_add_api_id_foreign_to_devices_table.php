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
        Schema::table('devices', function (Blueprint $table) {
            // First, drop the existing foreign key constraint
            $table->dropForeign(['Api_Id']); // Drop old foreign key
            
            // Re-add the foreign key with onDelete('restrict')
            $table->foreign('Api_Id')
                ->references('Api_Id')
                ->on('api_accounts')
                ->onDelete('restrict');  // Prevent deletion if there are related devices
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Reverse the process: drop the 'restrict' constraint
            $table->dropForeign(['Api_Id']); // Drop updated foreign key
            
            // Re-add the original foreign key (with cascade delete)
            $table->foreign('Api_Id')
                ->references('Api_Id')
                ->on('api_accounts')
                ->onDelete('cascade');  // Restore original behavior
        });
    }
};
