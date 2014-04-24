<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFlightsTable extends Migration {

	public function up()
	{
		Schema::create('flights', function(Blueprint $table) {
			$table->increments('id');
			$table->date('startdate');
			$table->string('callsign', 10);
			$table->tinyInteger('callsign_type')->default(0);
			$table->string('airline_id')->nullable();
			$table->mediumInteger('vatsim_id')->index();
			$table->string('departure_id',6);
			$table->string('arrival_id',6);
			$table->string('departure_country_id',2);
			$table->string('arrival_country_id',2);
			$table->text('route');
			$table->text('remarks');
			$table->string('altitude', 15);
			$table->smallInteger('speed');
			$table->char('flighttype',1)->default('I');
			$table->tinyInteger('state')->index();
			$table->boolean('missing')->default(false);
			$table->string('aircraft_code', 20);
			$table->string('aircraft_id', 20)->nullable()->default(null);
			$table->datetime('departure_time')->nullable()->default(null);
			$table->datetime('arrival_time')->nullable()->default(null);
			$table->smallInteger('duration')->default(0);
			$table->smallInteger('distance')->default(0);
			$table->decimal('last_lat', 10,6);
			$table->decimal('last_lon', 10,6);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('flights');
	}
}