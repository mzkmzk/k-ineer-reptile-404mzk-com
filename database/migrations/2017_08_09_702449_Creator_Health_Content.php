<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatorHealthContent extends Migration
{

    public function up()
    {
        Schema::create('Creator_Health_Content', function (Blueprint $table) {
            $table->increments('id');

$table->dateTime('created_at');

$table->dateTime('updated_at');

$table->dateTime('deleted_at');

$table->string('title','255');

$table->string('type','255');

$table->string('pic_url','255');

$table->string('video_url','255');

$table->string('url','255');


        });
    }

    public function down()
    {
        Schema::drop('Creator_Health_Content');
    }
}
