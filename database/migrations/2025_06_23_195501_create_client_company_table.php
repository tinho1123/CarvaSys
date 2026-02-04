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
        Schema::create('client_company', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('company_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('client_id', 'fk_client_company_client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('company_id', 'fk_client_company_company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unique(['client_id', 'company_id']);
            $table->index(['client_id', 'company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_company');
    }
};
