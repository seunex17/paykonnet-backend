<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_06_05_072903_add_columns_to_users_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/5/26
 * Time: 8:29 AM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('firstname')->nullable()->after('name');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('picture')->nullable()->after('lastname');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('lockscreen')->nullable()->after('is_active');
            $table->string('transfer_pin')->nullable()->after('lockscreen');
            $table->string('id_type')->nullable()->after('transfer_pin');
            $table->string('id_number')->nullable()->after('id_type');
            $table->boolean('id_verified')->default(false)->after('id_number');
            $table->string('agent_level')->nullable()->after('id_verified');
            $table->date('next_agent_payment_date')->nullable()->after('agent_level');
            $table->string('mono_id')->nullable()->after('next_agent_payment_date');
            $table->string('mono_mandate')->nullable()->after('mono_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'username',
                'firstname',
                'lastname',
                'picture',
                'is_active',
                'lockscreen',
                'transfer_pin',
                'id_type',
                'id_number',
                'id_verified',
                'agent_level',
                'next_agent_payment_date',
                'mono_id',
                'mono_mandate',
            ]);
        });
    }
};
