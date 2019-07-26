<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/26
 * Time: 20:02
 */

namespace App\Library;


use itbdw\Ip\IpLocation;

class LocationHelper
{
    static function check(string $ip) {
        $location = IpLocation::getLocation($ip);
        if ($ip == "127.0.0.1" || ($location["country"] == "中国" && $location["province"] == "江苏"))
            return true;
        else
            return false;
    }
}