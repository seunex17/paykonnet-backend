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
        Schema::create('cash_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->foreignId('debit_card_id')->nullable()->constrained()->onDelete('set null');
            $table->string('device_id')->nullable();
            $table->string('nin')->unique()->nullable(); // National Identification Number
            $table->string('guarantor_email')->nullable();
            $table->string('guarantor_phone_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('repayment_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->boolean('fully_paid')->default(false);
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_loans');
    }
};
