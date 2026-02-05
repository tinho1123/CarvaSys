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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name'); // Restaurante, Mercado, Farmácia, Bebidas
            $table->string('logo_path')->nullable()->after('type');
            $table->string('banner_path')->nullable()->after('logo_path');
            $table->text('description')->nullable()->after('name');
            $table->decimal('rating', 3, 1)->default(0)->after('banner_path');
            $table->string('delivery_time')->nullable()->after('rating');
            $table->boolean('is_promoted')->default(false)->after('delivery_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'logo_path',
                'banner_path',
                'description',
                'rating',
                'delivery_time',
                'is_promoted',
            ]);
        });
    }
};
