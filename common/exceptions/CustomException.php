<?php

namespace app\common\exceptions;

use app\common\log\Log;
use yii\base\UserException;
use app\common\errors\BaseError;

class CustomException extends UserException
{
    protected $message = "系统异常";
    protected $code = -10000;
    protected $data = null;

    /**
     * @param string $method 相关模块，利于异常日志排查
     * @param string $code 错误码
     * @param null $data 相关参数
     * @param array $message 错误提示
     * @eg  throw new CustomException(__METHOD__,BaseError::API_TIMEOUT,'接口超时');
     */
    public function __construct($method,$code,$message = array(),$data = null)
    {
        $this->data = $data;
        $this->code = $code;
        $this->message = BaseError::getError($code);
        $message = empty($message) ? $this->message : $message;
        $this->message = is_array($message) ? implode(' | ',$message) : $message;

        Log::error('Exception %s | msg = %s | code= %s | data= %s',[$method,$this->message,$this->code,$data],__METHOD__);
        parent::__construct($this->message, $this->code, null);
    }

    public function getName()
    {
        return __CLASS__;
    }

    public function getData()
    {
        return $this->data;
    }
}