<?php
	/**
	 * Copyright (C) ZubDev Digital Media - All Rights Reserved
	 *
	 * File: 2026_07_15_074019_update_column_in_transactions_table.php
	 * Author: Zubayr Ganiyu
	 *   Email: <seunexseun@gmail.com>
	 *   Website: https://zubdev.net
	 * Date: 7/15/26
	 * Time: 8:40 AM
	 */


	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration
	{
		public function up()
		: void
		{
			Schema::table('transactions', function(Blueprint $table)
			{
				$table->string('source_table')->nullable()->change();
			});
		}


		public function down()
		: void
		{
			Schema::table('transactions', function(Blueprint $table)
			{
				//
			});
		}
	};
