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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companies_users')->references('id')->on('companies_users')->onUpdate('CASCADE');
            $table->string('name');
            $table->longText('description');
            $table->decimal('amount', 8, 2, true);
            $table->decimal('discounts', 8,2,true);
            $table->enum('active', ['Y', 'N'])->default('Y');
            $table->decimal('total_amount', 8, 2, true);
            $table->bigInteger('quantity', false, true);
            $table->string('image')->nullable();
            $table->enum('isCool', ['Y', 'N']);
            $table->foreignId('category_id')->references('id')->on('products_categories')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
