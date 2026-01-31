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
        Schema::create('favored_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->uuid('favored_id')->nullable();
            $table->uuid('product_id')->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('discounts', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('favored_total', 10, 2);
            $table->decimal('favored_paid_amount', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->string('image', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->string('category_name', 255)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('client_name', 255)->nullable();
            $table->uuid('client_id')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'favored_id']);
            $table->index(['favored_id']);
            $table->index(['product_id']);
            $table->index(['company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favored_transactions');
    }
};
