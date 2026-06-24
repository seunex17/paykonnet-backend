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
        Schema::create('flex_data_lists', function (Blueprint $table) {
            $table->id();
            $table->string('service_id')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('duration')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flex_data_lists');
    }
};
