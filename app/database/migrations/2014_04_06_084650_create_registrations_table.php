<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRegistrationsTable extends Migration {

	public function up()
	{
		Schema::create('registrations', function(Blueprint $table) {
			$table->increments('id');
			$table->string('prefix', 5)->unique();
			$table->string('country_id',2);
		});
	}

	public function down()
	{
		Schema::drop('registrations');
	}
}