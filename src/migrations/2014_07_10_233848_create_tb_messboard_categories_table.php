<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTbMessboardCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_messboard_categories', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name', 30)->unique();
            $table->string('default_pic', 100)->nullable()->default(null);
            $table->boolean('private')->default(false);
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
        Schema::drop('tb_messboard_categories');
    }
}
