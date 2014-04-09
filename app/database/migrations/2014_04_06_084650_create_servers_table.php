<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServersTable extends Migration {

	public function up()
	{
		Schema::create('servers', function(Blueprint $table) {
			$table->increments('id');
			$table->text('path');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('servers');
	}
}