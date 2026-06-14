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
        if (!Schema::hasColumn('loans', 'loan_type')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->string('loan_type')->nullable()->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('loans', 'loan_type')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('loan_type');
            });
        }
    }
};
