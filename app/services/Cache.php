<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/7/29
 * Time: 下午3:10
 */
namespace Services;

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class Cache
{

    static $key;

    public static function set($key,$life=120)
    {
        self::$key = $key;
        $frontCache = new FrontData(["lifetime" => $life]);
        return new BackFile($frontCache, ["cacheDir" => "../app/cache/"]);
        $robots = $cache->get($key);
        $cache->save($cacheKey, $robots);
    }

}