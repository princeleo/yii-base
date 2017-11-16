<?php
/**
 * Author: Richard <chenz@snsshop.cn>
 * Date: 2016/11/25
 * Time: 14:31
 */

namespace app\common\log;

use yii\log\Target;
use yii\log\Logger;

class SglTarget extends Target
{
    protected $host;
    protected $port;
    protected $head_len = 13;
    protected $version = 1;
    protected $cmd = 100;
    protected $appid = 181;
    protected $src = 0;
    protected $timeout = 1;

    protected static $fp;

    protected static $configurable = [
        'host', 'port', 'timeout', 'appid'
    ];

    const LOG_ALERT = 1;
    const LOG_CRIT = 2;
    const LOG_EMER = 0; //紧急处理日志：重要到业务出错
    const LOG_ERROR = 3;  //错误日志：PHP错误 && 异常错误 && 系统级别日志
    const LOG_WARNING = 4;  //警告日志：不影响业务流程，但比较重要日志
    const LOG_NOTICE = 5;  //数据中心业务上报
    const LOG_INFO = 6; //记录的日志：普通日志，系统日志
    const LOG_DEBUG = 7;  //调试日志 && 性能评测日志
    const LOG_TRACE = 8;  //跟踪日志：适用于业务跟踪

    protected static $levelMap = array(
        0 => self::LOG_EMER,
        Logger::LEVEL_TRACE => self::LOG_TRACE,
        Logger::LEVEL_PROFILE_BEGIN => self::LOG_DEBUG,
        Logger::LEVEL_PROFILE_END => self::LOG_DEBUG,
        Logger::LEVEL_PROFILE => self::LOG_NOTICE,
        Logger::LEVEL_INFO => self::LOG_INFO,
        Logger::LEVEL_WARNING => self::LOG_WARNING,
        Logger::LEVEL_ERROR => self::LOG_ERROR,
    );

    public function __construct(array $config)
    {
        foreach (self::$configurable as $v) {
            if (isset($config[$v])) {
                $this->{$v} = $config[$v];
                unset($config[$v]);
            }
        }
        parent::__construct($config);
    }

    public function export()
    {
        $levelContent = [];
        foreach ($this->messages as $message) {
            $level = $this->getDestinationLevel($message[1]);
            if (! isset($levelContent[$level])) {
                $levelContent[$level] = [];
            }
            $levelContent[$level][] = $this->formatMessage($message);
        }
        foreach ($levelContent as $level => $item) {
            $content = implode($item, PHP_EOL);
            $this->writeLog($this->appid, $level, trim($content), $this->src);
        }
    }

    private function getDestinationLevel($level)
    {
        return isset(self::$levelMap[$level]) ? self::$levelMap[$level] : self::LOG_NOTICE;
    }

    private function writeLog($appid, $level, $content, $src = 0)
    {
        $errMsg = '';
        $contentlen = strlen($content) + 1;
        $pkglen = $this->head_len + $contentlen;
        $buffer = pack("n6Ca$contentlen", $pkglen, $this->version, $this->cmd, $src, 0, $appid, $level, $content);
        $this->tcpSend($buffer, $pkglen, $errMsg, $this->host, $this->port, $this->timeout);
    }

    private function tcpSend($strSend, $iSendLen, & $sErrMsg, $strAddress, $iPort, $iTimeout = 1)
    {
        $errno = 0;
        $errstr = "";

        if (! is_resource(self::$fp)) {
            self::$fp = fsockopen('tcp://' . $strAddress, $iPort, $errno, $errstr, $iTimeout);
        }

        $fp = self::$fp;

        if (!$fp) {
            $sErrMsg = "ERROR: $errno - $errstr";
            return false;
        }

        stream_set_timeout($fp, $iTimeout);
        $ret = fwrite($fp, $strSend, $iSendLen);

        if ($ret != $iSendLen) {
            $sErrMsg = "fwrite failed. ret:[$ret]";
            if (isset($stream_info['timed_out'])) {
                $sErrMsg .= ' socket_timed_out';
            }
            return false;
        }

        fclose($fp);

        return true;
    }
}