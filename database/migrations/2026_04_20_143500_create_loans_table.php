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
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->string('type')->default('loan'); // loan, repayment
                $table->date('loan_date');
                $table->text('description')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
