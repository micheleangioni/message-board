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
            $table->integer('category_id')->unique()->nullable()->default(null);
            $table->integer('user_id')->unsigned();
            $table->integer('poster_id')->index()->unsigned()->nullable();
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
        Schema::drop('tb_messboard_posts');
    }

}
