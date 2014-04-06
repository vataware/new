<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRoutesTable extends Migration {

	public function up()
	{
		Schema::create('routes', function(Blueprint $table) {
			$table->increments('id');
			$table->string('departure_id',6);
			$table->string('arrival_id',6);
			$table->text('route');
			$table->timestamps();

			$table->index(['departure_id','arrival_id']);
		});
	}

	public function down()
	{
		Schema::drop('routes');
	}
}