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
        Schema::create('ussd_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('number')->unique();
            $table->string('identifier')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->boolean('validated')->default(false);
            $table->string('service')->nullable();
            $table->string('code')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_cards');
    }
};
