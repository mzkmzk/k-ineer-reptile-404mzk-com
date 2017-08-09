<?php

namespace App\Entities;

use K_Laravel_Creator\Entities\Base_Entity;


class Creator_Url_Entity extends Base_Entity{

    public static $entity = [
        "Url" => "url信息"
    ];

    public static function get_attribute(){
        $attribute = array();
        $attribute['url'] = parent::set_attribute("url","string");
        $attribute['type'] = parent::set_attribute("类型","string");
        $attribute['belong'] = parent::set_attribute("归属业务","string");
        $attribute['weight'] = parent::set_attribute("权重","int");
        $attribute['last_visit_at'] = parent::set_attribute("上次访问时间","date");
        $attribute['visit_interval'] = parent::set_attribute("访问频率","int");
        $attribute['referer'] = parent::set_attribute("来源","string");
        return array_merge(parent::get_attribute(),$attribute);
    }
}