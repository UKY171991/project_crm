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
        if (!Schema::hasColumn('client_feedback', 'next_schedule')) {
            Schema::table('client_feedback', function (Blueprint $table) {
                $table->dateTime('next_schedule')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('client_feedback', 'next_schedule')) {
            Schema::table('client_feedback', function (Blueprint $table) {
                $table->dropColumn('next_schedule');
            });
        }
    }
};
