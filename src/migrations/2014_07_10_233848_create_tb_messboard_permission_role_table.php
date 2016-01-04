<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTbMessboardPermissionRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tb_messboard_permission_role', function(Blueprint $table)
		{
			$table->integer('permission_id')->unsigned();
			$table->integer('role_id')->unsigned();
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
		Schema::drop('tb_messboard_permission_role');
	}

}
