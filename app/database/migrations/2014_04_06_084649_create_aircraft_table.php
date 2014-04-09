<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAircraftTable extends Migration {

	public function up()
	{
		Schema::create('aircraft', function(Blueprint $table) {
			$table->increments('id');
			$table->char('type',1);
			$table->string('manufacturer', 50);
			$table->string('model', 30);
			$table->string('code', 4);
			$table->tinyInteger('engine_quantity')->nullable()->default(null);
			$table->char('engine_type', 1)->nullable()->default(null);
			$table->char('weight_class', 1)->nullable()->default(null);
			$table->string('descent_rate', 10)->nullable()->default(null);
			$table->string('service_ceiling', 10)->nullable()->default(null);
			$table->string('cruise_tas', 10)->nullable()->default(null);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('aircraft');
	}
}