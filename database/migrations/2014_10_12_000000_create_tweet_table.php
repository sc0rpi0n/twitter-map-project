<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_str');           // id_str of the tweet
            $table->string('SearchLocation');   // the location query
            $table->text('tweet');              // the tweet
            $table->string('lat');              // latitude data
            $table->string('lng');              // longitude data
            $table->string('createdAt');        // tweet created at
            $table->string('userPic');          // url to user pic
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
        Schema::drop('tweets');
    }
}
