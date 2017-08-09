<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatorSetting extends Migration
{

    public function up()
    {
        Schema::create('Creator_Setting', function (Blueprint $table) {
            $table->increments('id');

$table->dateTime('created_at');

$table->dateTime('updated_at');

$table->dateTime('deleted_at');

$table->string('belong','255');

$table->string('type','255');

$table->integer('limit_num');


        });
    }

    public function down()
    {
        Schema::drop('Creator_Setting');
    }
}
