<?php

namespace app\common\log;
use app\common\helpers\BaseHelper;
use yii\log\Logger;


/**
 *
 * 格式一：Log::warning('用户名%s | 参数%s',['name' => ['111','2222'],'kk'],__METHOD__);  ===>支持printf格式替换
 * 格式二：Log::warning('这是一个样例',['name' => ['111','2222'],'kk']);  ====>$data参数会json化后连接到第一个参数后
 * 间隔符“ | ”
 *
 * 所有的日志请按级别业务调用不同的方法：
 * @debug 调试日志 && 性能评测日志
 * @trace 跟踪日志：适用于业务跟踪
 * @info 记录的日志：普通日志，系统日志
 * @notice 数据中心业务上报
 * @waring 警告日志：不影响业务流程，但比较重要日志
 * @error 错误日志：PHP错误 && 异常错误 && 系统级别日志
 * @emer 紧急处理日志：重要到业务出错
 * Class Log
 * @package app\common\log
 */
class Log
{
    const CATEGORY = 'application';

    /**
     * 调试日志
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function debug($message,$data = array(),$category = self::CATEGORY)
    {
        if (YII_DEBUG) {
            $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
            \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_TRACE, $category);
        }
    }

    /**
     * 跟踪日志：适用于业务跟踪
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function trace($message,$data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_TRACE, $category);
    }

    /**
     * 错误日志：PHP错误 && 异常错误
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function error($message, $data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_ERROR, $category);
    }

    /**
     * 警告日志：不影响业务流程，但比较重要日志
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function warning($message, $data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_WARNING, $category);
    }

    /**
     * 记录的日志：普通日志
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function info($message, $data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_INFO, $category);
    }


    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[endProfile]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ~~~
     * \Yii::beginProfile('block1');
     * // some code to be profiled
     *     \Yii::beginProfile('block2');
     *     // some other code to be profiled
     *     \Yii::endProfile('block2');
     * \Yii::endProfile('block1');
     * ~~~
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see endProfile()
     */
    public static function begin($token, $category = self::CATEGORY)
    {
        \Yii::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[beginProfile]] with the same category name.
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see beginProfile()
     */
    public static function end($token, $category = self::CATEGORY)
    {
        \Yii::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
    }

    /**
     * 上报ES日志
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function report($message, $data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), Logger::LEVEL_PROFILE, $category);
    }

    /**
     * 紧急处理日志：重要到业务出错
     * @param $message
     * @param $data
     * @param string $category
     */
    public static function emer($message ,$data = array(), $category = self::CATEGORY)
    {
        $category = is_string($data) && $category == self::CATEGORY ? $data : $category;
        \Yii::getLogger()->log(self::formatMsg($message,$data), 0, $category);
    }


    /**
     * 格式化日志
     * @param $message
     * @param array $data
     * @return string
     */
    private static function formatMsg($message,$data = [])
    {
        if(substr_count($message,'%') > 0){
            return BaseHelper::str_vsprintf($message,$data);
        }elseif(!empty($data) && is_array($data)){
            return $message = $message.' | '.json_encode($data);
        }elseif(!empty($data) && is_string($data)){
            return $message = $message.' | '.$data;
        }else{
            return $message;
        }
    }
}

