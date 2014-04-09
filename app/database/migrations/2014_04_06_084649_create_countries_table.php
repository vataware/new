<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration {

	public function up()
	{
		Schema::create('countries', function(Blueprint $table) {
			$table->string('id', 2)->primary();
			$table->string('country');
			$table->boolean('cleaned');
		});
	}

	public function down()
	{
		Schema::drop('countries');
	}
}