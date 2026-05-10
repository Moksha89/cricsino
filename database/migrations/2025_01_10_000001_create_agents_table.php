<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->string('role')->default('agent'); // super_admin, master, agent
            $table->string('code')->unique(); // agent code for referrals
            $table->decimal('commission_rate', 8, 4)->default(0);
            $table->decimal('credit_limit', 16, 2)->default(0);
            $table->decimal('balance', 16, 2)->default(0);
            $table->decimal('exposure', 16, 2)->default(0);
            $table->integer('max_users')->default(100);
            $table->integer('max_sub_agents')->default(10);
            $table->boolean('active')->default(true);
            $table->boolean('can_create_users')->default(true);
            $table->boolean('can_manage_bets')->default(false);
            $table->boolean('can_manage_deposits')->default(false);
            $table->json('allowed_sports')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agent_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('related_agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->string('type'); // credit, debit, commission, settlement
            $table->decimal('amount', 16, 2);
            $table->decimal('balance_before', 16, 2);
            $table->decimal('balance_after', 16, 2);
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_transactions');
        Schema::dropIfExists('agents');
    }
};
