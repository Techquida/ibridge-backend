<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('exam_board', 10)->index();          // WAEC | JAMB
            $table->string('subject', 50)->index();             // Mathematics | English | Biology
            $table->string('topic', 100)->index();
            $table->unsignedSmallInteger('year')->nullable();   // exam year (optional)
            $table->text('question_text');
            $table->json('options');                            // array of 4 option strings
            $table->unsignedTinyInteger('correct_answer');      // 0–3 index — NEVER in API response
            $table->text('explanation')->nullable();
            $table->string('difficulty', 10)->default('medium'); // easy|medium|hard
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Compound indexes for fast stratified random queries
            $table->index(['subject', 'exam_board']);
            $table->index(['subject', 'exam_board', 'difficulty']);
            $table->index(['subject', 'exam_board', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
