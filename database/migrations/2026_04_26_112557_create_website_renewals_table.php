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
        Schema::create('website_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('type'); // domain, hosting, ssl, other
            $table->date('renewal_date');
            $table->date('new_expiry_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->string('payment_status')->default('Paid'); // Paid, Pending, Partially Paid
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_renewals');
    }
};
