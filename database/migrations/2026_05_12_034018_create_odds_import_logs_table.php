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
        Schema::create('odds_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sport_key');
            $table->integer('games_imported')->default(0);
            $table->integer('odds_imported')->default(0);
            $table->string('status')->default('running');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->string('trigger_source')->default('manual');
            $table->timestamps();
        });

        \App\Models\Setting::firstOrCreate(
            ['name' => 'theoddsapi_api_key', 'group' => 'site'],
            ['val' => null]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds_import_logs');
    }
};
