<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTbMessboardPostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tb_messboard_posts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('post_type', 15);
			$table->integer('user_id')->unsigned()->index('`mp_user`');
			$table->integer('poster_id')->unsigned()->nullable();
			$table->text('text', 65535);
			$table->boolean('is_read')->default(1);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tb_messboard_posts');
	}

}