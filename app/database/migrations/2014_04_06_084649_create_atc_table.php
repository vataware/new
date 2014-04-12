<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAtcTable extends Migration {

	public function up()
	{
		Schema::create('atc', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('vatsim_id');
			$table->string('callsign', 10);
			$table->datetime('start');
			$table->datetime('end')->nullable();
			$table->double('lat',10,6);
			$table->double('lon',10,6);
			$table->string('airport_id',6)->nullable()->default(null);
			$table->smallInteger('visual_range');
			$table->double('frequency',6,3);
			$table->smallInteger('facility_id')->unsigned();
			$table->smallInteger('rating_id')->unsigned();
			$table->datetime('time');
			$table->smallInteger('duration')->default(0);
			$table->boolean('missing')->default(false);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('atc');
	}
}