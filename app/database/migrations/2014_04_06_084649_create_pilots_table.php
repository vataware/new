<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePilotsTable extends Migration {

	public function up()
	{
		Schema::create('pilots', function(Blueprint $table) {
			$table->increments('id');
			$table->mediumInteger('vatsim_id');
			$table->string('name');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('aircraft');
	}
}