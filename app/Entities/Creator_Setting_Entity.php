<?php

namespace App\Entities;

use K_Laravel_Creator\Entities\Base_Entity;


class Creator_Setting_Entity extends Base_Entity{

    public static $entity = [
        "Setting" => "设置信息"
    ];

    public static function get_attribute(){
        $attribute = array();
        $attribute['belong'] = parent::set_attribute("belong","string");
        $attribute['type'] = parent::set_attribute("类型","string");
        $attribute['limit_num'] = parent::set_attribute("限制次数","int");
        return array_merge(parent::get_attribute(),$attribute);
    }
}