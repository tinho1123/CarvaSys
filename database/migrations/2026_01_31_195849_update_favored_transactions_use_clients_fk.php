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
        Schema::table('favored_transactions', function (Blueprint $table) {
            $table->dropColumn('favored_id');
        });

        // Drop the favoreds table
        Schema::dropIfExists('favoreds');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create favoreds table again
        Schema::create('favoreds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('document')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('type', ['individual', 'company'])->default('individual');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('favored_transactions', function (Blueprint $table) {
            // Add back favored_id
            $table->foreignId('favored_id')->nullable()->after('company_id')->constrained('favoreds')->onDelete('cascade');
            // Drop client_id
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
