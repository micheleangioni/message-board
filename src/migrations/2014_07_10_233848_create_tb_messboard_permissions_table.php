<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTbMessboardPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tb_messboard_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 30)->unique('`mb_permissions_name_unique`');
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
		Schema::drop('tb_messboard_permissions');
	}

}
