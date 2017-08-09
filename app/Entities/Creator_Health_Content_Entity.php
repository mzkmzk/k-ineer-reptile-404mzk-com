<?php

namespace App\Entities;

use K_Laravel_Creator\Entities\Base_Entity;


class Creator_Health_Content_Entity extends Base_Entity{

    public static $entity = [
        "Health_Content" => "健康网站信息"
    ];

    public static function get_attribute(){
        $attribute = array();
        $attribute['title'] = parent::set_attribute("title","string");
        $attribute['type'] = parent::set_attribute("类型","string");
        $attribute['pic_url'] = parent::set_attribute("pic_url","string");
        $attribute['video_url'] = parent::set_attribute("video_url","string");
        $attribute['url'] = parent::set_attribute("所在网址","string");
        return array_merge(parent::get_attribute(),$attribute);
    }
}