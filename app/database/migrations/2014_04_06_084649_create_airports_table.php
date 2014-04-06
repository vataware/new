<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAirportsTable extends Migration {

	public function up()
	{
		Schema::create('airports', function(Blueprint $table) {
			$table->string('id', 6)->primary();
			$table->string('iata', 3)->nullable()->index();
			$table->string('name', 255);
			$table->string('country_id',2)->index();
			$table->decimal('lat', 10,6);
			$table->decimal('lon', 10,6);
			$table->integer('elevation');
			$table->string('city');
			$table->string('fir', 4)->nullable()->default(null);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('airports');
	}
}