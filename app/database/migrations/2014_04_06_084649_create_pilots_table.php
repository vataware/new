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
			$table->smallInteger('rating_id')->default(1);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('aircraft');
	}
}