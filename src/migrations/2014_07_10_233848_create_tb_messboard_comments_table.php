<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTbMessboardCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tb_messboard_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('post_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->text('text', 65535);
			$table->nullableTimestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tb_messboard_comments');
	}

}
