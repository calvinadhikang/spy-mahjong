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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('username');
            $table->integer('total_xp')->default(0)->after('is_admin');
        });

        Schema::create('xp_reward_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('first_place_xp')->default(100);
            $table->integer('second_place_xp')->default(60);
            $table->integer('third_place_xp')->default(30);
            $table->integer('fourth_place_xp')->default(10);
            $table->integer('loss_xp')->default(0);
            $table->timestamps();
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('min_xp');
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->unique('name');
            $table->unique('min_xp');
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->string('scoring_rule_version')->nullable()->after('completed_at');
        });

        Schema::create('game_session_player_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('placement');
            $table->string('outcome');
            $table->integer('xp_earned');
            $table->decimal('score', 12, 2)->nullable();
            $table->string('scoring_rule_version');
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->unique(['game_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_session_player_results');

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn('scoring_rule_version');
        });

        Schema::dropIfExists('levels');
        Schema::dropIfExists('xp_reward_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'total_xp']);
        });
    }
};
