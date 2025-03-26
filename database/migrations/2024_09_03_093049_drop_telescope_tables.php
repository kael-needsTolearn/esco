<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function getConnection(): ?string
    {
        return config('telescope.storage.database.connection');
    }
    public function up(): void
    {
        //
        $schema = Schema::connection($this->getConnection());

        $schema->dropIfExists('telescope_entries_tags');
        $schema->dropIfExists('telescope_entries');
        $schema->dropIfExists('telescope_monitoring');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
