<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('team', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('name');
			$table->string('job');
			$table->string('email')->nullable()->default(null);
			$table->string('facebook')->nullable()->default(null);
			$table->string('twitter')->nullable()->default(null);
			$table->text('description')->nullable()->default(null);

			$table->tinyInteger('priority');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('team');
	}

}
