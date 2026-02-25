<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('subscription_expiry');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('subscription_expiry');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('commission_duration_months');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_suspended'));
        Schema::table('schools', fn (Blueprint $t) => $t->dropColumn('is_suspended'));
        Schema::table('partners', fn (Blueprint $t) => $t->dropColumn('is_active'));
    }
};
