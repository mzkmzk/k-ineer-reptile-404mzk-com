<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatorResource extends Migration
{

    public function up()
    {
        Schema::create('Creator_Resource', function (Blueprint $table) {
            $table->increments('id');

$table->dateTime('created_at');

$table->dateTime('updated_at');

$table->dateTime('deleted_at');

$table->string('local_path','255');

$table->string('url','255');


        });
    }

    public function down()
    {
        Schema::drop('Creator_Resource');
    }
}
