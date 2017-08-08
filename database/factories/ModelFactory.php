<?php

function get_all_id(K_Laravel_Creator\Models\Base_Model $base_mobel) {
    /* dump($base_mobel);
     dump(\App\Models\User_Model::query()->get());
     error_log(\App\Models\User_Model::all());*/
    $model_datas = $base_mobel->all();
    //error_log($model_datas);
    $model_id = array();
    foreach($model_datas as $model_data){
        array_push($model_id,$model_data['id']);
    }
    return $model_id;
}

$factory->define(App\Models\Creator_User_Model::class, function (Faker\Generator $faker) {
    return [
        'created_at' => $faker->dateTime(),
'updated_at' => $faker->dateTime(),
'wechat_id' => $faker->text(30),
'login_sum' => $faker->numberBetween(),
'visit_password' => $faker->text(30),
'sina_uid' => $faker->text(30),
'sina_access_token' => $faker->text(30),

    ];
});

$factory->define(App\Models\Creator_User_Model::class, function (Faker\Generator $faker) {
    return [
        'created_at' => $faker->dateTime(),
'updated_at' => $faker->dateTime(),
'wechat_id' => $faker->text(30),
'login_sum' => $faker->numberBetween(),
'visit_password' => $faker->text(30),
'sina_uid' => $faker->text(30),
'sina_access_token' => $faker->text(30),

    ];
});

$factory->define(App\Models\Creator_User_Model::class, function (Faker\Generator $faker) {
    return [
        'created_at' => $faker->dateTime(),
'updated_at' => $faker->dateTime(),
'wechat_id' => $faker->text(30),
'login_sum' => $faker->numberBetween(),
'visit_password' => $faker->text(30),
'sina_uid' => $faker->text(30),
'sina_access_token' => $faker->text(30),

    ];
});

