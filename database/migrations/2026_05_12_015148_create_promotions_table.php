<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type'); // welcome, deposit, cashback, manual, referral
            $table->string('status')->default('active'); // active, inactive, expired
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->decimal('min_deposit', 10, 2)->nullable();
            $table->decimal('max_bonus', 10, 2)->nullable();
            $table->decimal('bonus_percent', 5, 2)->nullable();
            $table->decimal('fixed_bonus_amount', 10, 2)->nullable();
            $table->decimal('wagering_multiplier', 5, 2)->nullable();
            $table->string('eligible_user_level')->nullable();
            $table->unsignedInteger('claim_limit_per_user')->nullable();
            $table->unsignedInteger('total_claim_limit')->nullable();
            $table->text('terms_text')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
