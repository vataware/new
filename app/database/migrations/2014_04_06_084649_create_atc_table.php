<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAtcTable extends Migration {

	public function up()
	{
		Schema::create('atc', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('vatsim_id');
			$table->string('callsign', 10);
			$table->datetime('start');
			$table->datetime('end')->nullable();
			$table->smallInteger('facility')->unsigned();
			$table->integer('rating_id')->unsigned();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('atc');
	}
}