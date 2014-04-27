<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePositionsTable extends Migration {

	public function up()
	{
		Schema::create('positions', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('flight_id')->unsigned();
			$table->integer('update_id')->unsigned();
			$table->decimal('lat', 10,6);
			$table->decimal('lon', 10,6);
			$table->mediumInteger('altitude');
			$table->smallInteger('ground_elevation');
			$table->smallInteger('speed');
			$table->smallInteger('heading');
			$table->dateTime('time');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('positions');
	}
}