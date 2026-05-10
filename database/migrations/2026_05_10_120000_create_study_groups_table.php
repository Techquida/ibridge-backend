<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->string('emoji')->default('📚');
            $table->string('invite_code', 10)->unique();
            // If not null, this group was created by a school and only that school's students can join
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            // The user who created the group (null when school creates it)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('study_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamps();

            $table->unique(['study_group_id', 'user_id']);
        });

        Schema::create('study_group_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_group_messages');
        Schema::dropIfExists('study_group_members');
        Schema::dropIfExists('study_groups');
    }
};
