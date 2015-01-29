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
			$table->integer('permission_id')->unsigned()->index('`permission_role_permission_id_foreign`');
			$table->integer('role_id')->unsigned()->index('`permission_role_role_id_foreign`');
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
