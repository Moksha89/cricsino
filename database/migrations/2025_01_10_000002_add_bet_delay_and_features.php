<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add bet delay to games table
        Schema::table('games', function (Blueprint $table) {
            $table->integer('bet_delay')->default(0)->after('active'); // seconds
            $table->boolean('is_live')->default(false)->after('bet_delay');
            $table->string('stream_url')->nullable()->after('is_live');
            $table->string('scorecard_url')->nullable()->after('stream_url');
        });

        // Add agent reference to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('referral')->constrained('agents')->onDelete('set null');
            $table->string('whatsapp')->nullable()->after('phone');
        });

        // Create casino games table
        Schema::create('casino_games', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider'); // evolution, pragmatic_play, ezugi, betsoft
            $table->string('category'); // live_casino, slots, table_games, teen_patti, andar_bahar, roulette
            $table->string('game_id'); // provider's game ID
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('launch_url')->nullable();
            $table->boolean('is_live')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Create casino sessions table for tracking
        Schema::create('casino_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('casino_game_id')->constrained()->onDelete('cascade');
            $table->string('session_token')->nullable();
            $table->decimal('bet_amount', 16, 2)->default(0);
            $table->decimal('win_amount', 16, 2)->default(0);
            $table->decimal('balance_before', 16, 2)->default(0);
            $table->string('status')->default('active'); // active, completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casino_sessions');
        Schema::dropIfExists('casino_games');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
            $table->dropColumn('whatsapp');
        });
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['bet_delay', 'is_live', 'stream_url', 'scorecard_url']);
        });
    }
};
