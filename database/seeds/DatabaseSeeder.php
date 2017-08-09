<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{

    public function run()
    {
        Model::unguard();
        factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_User_Model::class, 1)->create();

factory(App\Models\Creator_Url_Model::class, 1)->create();

factory(App\Models\Creator_Health_Content_Model::class, 1)->create();

factory(App\Models\Creator_Resource_Model::class, 1)->create();

factory(App\Models\Creator_Setting_Model::class, 1)->create();

//
        Model::reguard();
    }

}
