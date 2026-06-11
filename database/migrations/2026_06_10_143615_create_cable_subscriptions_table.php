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
        Schema::create('cable_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('references')->unique();
            $table->string('provider_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('phone_no')->nullable();
            $table->string('smart_card_no')->nullable();
            $table->string('details')->nullable();
            $table->string('product')->nullable();
            $table->string('package')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_subscriptions');
    }
};
