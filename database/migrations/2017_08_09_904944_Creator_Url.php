<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatorUrl extends Migration
{

    public function up()
    {
        Schema::table('Creator_Url', function (Blueprint $table) {
            $table->string('belong','255');


        });
    }

    public function down()
    {
        Schema::drop('Creator_Url');
    }
}
