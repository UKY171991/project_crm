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
        if (!Schema::hasTable('websites')) {
            Schema::create('websites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('url')->nullable();
                $table->string('domain_name')->nullable();
                $table->date('domain_expiry_date')->nullable();
                $table->date('ssl_expiry_date')->nullable();
                $table->string('hosting_provider')->nullable();
                $table->string('server_ip')->nullable();
                $table->string('php_version')->nullable();
                $table->string('cms')->nullable();
                $table->string('admin_url')->nullable();
                $table->string('admin_username')->nullable();
                $table->string('admin_password')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
