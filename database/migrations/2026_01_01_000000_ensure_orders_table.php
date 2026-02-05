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
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->decimal('subtotal', 12, 2);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('fee_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
                $table->text('notes')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['company_id']);
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::dropIfExists('orders');
        }
    }
};
