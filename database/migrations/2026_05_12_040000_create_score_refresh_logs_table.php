<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('score_refresh_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sport_key');
            $table->integer('games_updated')->default(0);
            $table->integer('games_skipped')->default(0);
            $table->integer('errors_count')->default(0);
            $table->string('status')->default('running');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->string('trigger_source')->default('manual');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('score_refresh_logs');
    }
};
