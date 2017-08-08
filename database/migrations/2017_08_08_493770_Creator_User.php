<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatorUser extends Migration
{

    public function up()
    {
        Schema::create('Creator_User', function (Blueprint $table) {
            $table->increments('id');

$table->dateTime('created_at');

$table->dateTime('updated_at');

$table->dateTime('deleted_at');

$table->string('wechat_id','255');

$table->integer('login_sum');

$table->string('visit_password','255');

$table->string('sina_uid','255');

$table->string('sina_access_token','255');


        });
    }

    public function down()
    {
        Schema::drop('Creator_User');
    }
}
