<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAirlinesTable extends Migration {

	public function up()
	{
		Schema::create('airlines', function(Blueprint $table) {
			$table->increments('id');
			$table->string('icao', 10);
			$table->string('name');
			$table->string('radio')->nullable();
			$table->string('website')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('airlines');
	}
}