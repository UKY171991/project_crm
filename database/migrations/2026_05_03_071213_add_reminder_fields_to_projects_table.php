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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('reminder_frequency')->default('none')->after('status'); // none, daily, weekly, monthly
            $table->timestamp('last_reminder_at')->nullable()->after('reminder_frequency');
            $table->boolean('reminder_enabled')->default(false)->after('last_reminder_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['reminder_frequency', 'last_reminder_at', 'reminder_enabled']);
        });
    }
};
