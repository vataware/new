<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAircraftTable extends Migration {

	public function up()
	{
		Schema::create('aircraft', function(Blueprint $table) {
			$table->increments('id');
			$table->string('manufacturer', 50);
			$table->string('model', 30);
			$table->string('code', 4);
			$table->tinyInteger('engine_quantity');
			$table->char('engine_type', 1);
			$table->char('weight_class', 1);
			$table->string('descentrate', 10);
			$table->string('service_ceiling', 10);
			$table->string('cruise_tas', 10);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('aircraft');
	}
}