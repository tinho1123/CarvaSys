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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('document_type', ['cpf', 'cnpj'])->default('cpf');
            $table->string('document_number')->unique();
            $table->string('phone')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedSmallInteger('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['document_number', 'document_type']);
            $table->index(['email', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
