<?php

namespace App\Entities;

use K_Laravel_Creator\Entities\Base_Entity;


class Creator_Resource_Entity extends Base_Entity{

    public static $entity = [
        "Resource" => "视频"
    ];

    public static function get_attribute(){
        $attribute = array();
        $attribute['local_path'] = parent::set_attribute("本地路径","string");
        $attribute['url'] = parent::set_attribute("url","string");
        return array_merge(parent::get_attribute(),$attribute);
    }
}