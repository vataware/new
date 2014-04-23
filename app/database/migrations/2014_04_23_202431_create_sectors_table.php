<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sectors', function(Blueprint $table)
		{
			$table->increments('id');

			$table->string('code',10)->unique();
			$table->string('name',255);

			$table->timestamps();
		});

		Schema::create('sector_segments', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('sector_id')->unsigned();
			$table->smallInteger('sequence');
			$table->decimal('lat',10,6);
			$table->decimal('lon',10,6);
		});

		Schema::create('sector_aliases', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('sector_id')->unsigned();
			$table->string('code',10)->unique();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sectors');
		Schema::drop('sector_segments');
		Schema::drop('sector_aliases');
	}

}
