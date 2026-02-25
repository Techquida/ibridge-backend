<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add gamification + exam_board fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('exam_board')->nullable()->after('account_type'); // WAEC | JAMB
            $table->unsignedInteger('xp')->default(0)->after('exam_board');
            $table->unsignedInteger('streak_days')->default(0)->after('xp');
            $table->unsignedInteger('best_streak')->default(0)->after('streak_days');
            $table->date('last_activity_date')->nullable()->after('best_streak');
        });

        // Add richer session fields
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->unsignedInteger('total_questions')->default(0)->after('time_used');
            $table->string('exam_board')->nullable()->after('total_questions');
            $table->string('weakest_topic')->nullable()->after('exam_board');
            $table->json('time_per_question')->nullable()->after('weakest_topic');
            $table->boolean('dropped_before_submit')->default(false)->after('time_per_question');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['exam_board', 'xp', 'streak_days', 'best_streak', 'last_activity_date']);
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['total_questions', 'exam_board', 'weakest_topic', 'time_per_question', 'dropped_before_submit']);
        });
    }
};
