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
        Schema::table('game_market', function (Blueprint $table) {
            if (!Schema::hasColumn('game_market', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('bookie_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_market', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
