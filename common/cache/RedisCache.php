<?php
/**
 * Author: MaChenghang
 * Date: 2015/07/04
 * Time: 15:00
 * session类
 */
namespace app\common\cache;

use Yii;

/**
 * redis cache
 */
class RedisCache
{
    const SESSION_KEY_USER = '_Boss_';


    public static function bulid(){
        return Yii::$app->redis;
    }

    /**
     * 获取session
     * @return mixed
     */
    public static function get($key = null)
    {
        if(is_null($key)){
            return false;
        }
        $return = self::bulid()->get(self::SESSION_KEY_USER.$key);
        return json_decode($return) === null ? $return : json_decode($return,true);
    }

    /**
     * 设置session
     * @return mixed
     */
    public static function set($key = null, $value = null)
    {
        if(is_null($key) || is_null($value)){
            return false;
        }
        $value = is_array($value) ? json_encode($value) : $value;
        //RedisCache::bulid()->set($this->cache_key,$this->code,'EX', $this->expire, 'NX');
        return self::bulid()->set(self::SESSION_KEY_USER.$key, $value);
    }

    /**
     * 删除session
     * @return mixed
     */
    public static function del($key = null)
    {
        if(is_null($key)){
            return false;
        }
        return self::bulid()->set(self::SESSION_KEY_USER.$key, null);
    }

    /**
     * 注销session
     * @return mixed
     */
    public static function clear(){
        self::bulid()->open();
        self::bulid()->destroy();
        self::bulid()->close();
    }
}
