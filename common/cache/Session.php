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
 * session cache
 */
class Session
{
    const SESSION_KEY_USER = '_Boss_User_'; //此将影响到单点登录，不能随便改动


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
        $return = self::bulid()->get($key);
        return json_decode($return,true) == false ? $return : json_decode($return,true);
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
        return self::bulid()->set($key, $value);
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
        return self::bulid()->set($key, null);
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
