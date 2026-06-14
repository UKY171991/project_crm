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
            if (!Schema::hasColumn('projects', 'reminder_frequency')) {
                $table->string('reminder_frequency')->default('none')->after('status');
            }
            if (!Schema::hasColumn('projects', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('reminder_frequency');
            }
            if (!Schema::hasColumn('projects', 'reminder_enabled')) {
                $table->boolean('reminder_enabled')->default(false)->after('last_reminder_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('projects', 'reminder_frequency')) {
                $columnsToDrop[] = 'reminder_frequency';
            }
            if (Schema::hasColumn('projects', 'last_reminder_at')) {
                $columnsToDrop[] = 'last_reminder_at';
            }
            if (Schema::hasColumn('projects', 'reminder_enabled')) {
                $columnsToDrop[] = 'reminder_enabled';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
