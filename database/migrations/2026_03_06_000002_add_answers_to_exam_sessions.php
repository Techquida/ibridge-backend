<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Submitted answer indices (0–3) matching question order
            $table->json('answers')->nullable()->after('time_per_question');
            // Ordered list of question IDs used in the session (for audit/review)
            $table->json('question_ids')->nullable()->after('answers');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['answers', 'question_ids']);
        });
    }
};
