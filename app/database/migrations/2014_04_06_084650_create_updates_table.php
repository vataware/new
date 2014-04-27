<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUpdatesTable extends Migration {

	public function up()
	{
		Schema::create('updates', function(Blueprint $table) {
			$table->increments('id');
			$table->datetime('timestamp');
		});
	}

	public function down()
	{
		Schema::drop('updates');
	}
}