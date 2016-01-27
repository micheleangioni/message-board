<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbMessboardNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_messboard_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_id')->index()->unsigned()->nullable()->default(null);
            $table->string('from_type', 50)->nullable()->default(null);
            $table->integer('to_id')->index()->unsigned();
            $table->string('type', 50)->index()->nullable()->default(null);
            $table->text('text', 65535);
            $table->string('pic_url')->nullable()->default(null);
            $table->string('url')->nullable()->default(null);
            $table->string('extra')->nullable()->default(null);
            $table->boolean('read')->default(false);
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
        Schema::drop('tb_messboard_notifications');
    }
}
