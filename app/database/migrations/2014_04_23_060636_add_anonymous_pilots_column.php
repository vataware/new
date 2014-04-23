<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnonymousPilotsColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pilots', function(Blueprint $table)
		{
			$table->boolean('anonymous')->default(false)->after('rating_id');
			$table->boolean('exclude')->default(false)->after('hidden');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pilots', function(Blueprint $table)
		{
			$table->dropColumn('anonymous', 'exclude');
		});
	}

}
