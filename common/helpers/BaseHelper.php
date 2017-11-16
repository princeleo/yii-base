<?php

namespace app\common\helpers;

use app\common\errors\BaseError;
use yii;


class BaseHelper
{

    /**
     * 把activeRecord转成数组。
     * @param $activeRecord
     * @return array
     */
    public static function recordToArray($activeRecord)
    {
        $dataArr = [];
        if ($activeRecord instanceof \yii\db\ActiveRecord) {
            $dataArr = $activeRecord->toArray();
            self::finalToArr($activeRecord, $dataArr);
        } else {
            if (!is_array($activeRecord)) {
                return $activeRecord;
            }
            return self::recordListToArray($activeRecord);
        }
        return $dataArr;
    }


    /**
     * 实体转数组
     * @param $record
     * @param $ret
     */
    public static function finalToArr($record, &$ret)
    {
        if ($record->getRelatedRecords()) {
            foreach ($record->getRelatedRecords() as $key => $val) {
                if ($val instanceof \yii\db\ActiveRecord) {
                    $ret[$key] = $val->toArray();
                    self::finalToArr($val, $ret[$key]);
                } else if (count($val) > 0) {
                    $ret[$key] = self::recordListToArray($val);
                } else {
                    $ret[$key] = [];
                }
            }
        } else {
            $ret = $record->toArray();
        }
    }


    /**
     * 列表转换
     * @param $activeRecordList
     */
    public static function recordListToArray($activeRecordList)
    {
        if (!is_array($activeRecordList)) {
            return $activeRecordList;
        }
        foreach ($activeRecordList as $key => $val) {
            if ($val instanceof \yii\db\ActiveRecord) {
                $activeRecordList[$key] = self::recordToArray($val);
            } else {
                $activeRecordList[$key] = self::recordListToArray($val);
            }
        }
        return $activeRecordList;
    }

    /**
     * @param $length
     * @param $symbol  是否支持符号
     * @return null|string
     */
    public static function getRandChar($length,$symbol = true){
        $str = null;
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $strPol = $symbol == false ? $strPol : $strPol.'ABCDEFGHIJKLMNOPQRSTUVWXYZ,*&%#@!~^()-_+';
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 把金额由分转为元
     * @param $amount
     * @param int $digit
     * @return int|null|string
     */
    public static function amountFenToYuan($amount, $digit = 2)
    {
        if(is_null($amount) || empty($amount)) $amount =  '0.00';
        $amount = $amount/100;
        return number_format($amount, $digit, '.', '');
    }

    /**
     * 把金额由元转为分
     * @param $amount
     * @return float
     */
    public static function amountYuanToFen($amount){
        return ceil($amount*100);
    }

    /**
     * 统一json格式输出
     * @param $data
     * @param $code
     * @param $msg
     * @throws Exception
     */
    public static function result($data , $code = 0, $msg = '')
    {
        if(isset($data['retCode'])){
            $result = $data;
        }else{
            $code = (int)$code;
            $result = array(
                'retCode' => $code,
                'retMsg' => empty($msg) ? BaseError::getError($code) : $msg,
                'retData' =>  $data
            );
        }

        //注意：如果在此之前有输出，会出现空白
        header('Content-type:text/json');
        exit(json_encode($result));
    }


    /**
     * 判断主菜单是否展开
     * @param $route
     * @return bool
     */
    public static function checkMenuActive($route)
    {
        if(empty($route) || !is_array($route)) return false;

        $controller = Yii::$app->controller->id;
        foreach($route as $item){
            $rule = explode('/',$item['route']);
            if($controller == $rule[0]){
                return true;
            }
        }
        return false;
    }


    /**
     * 编译模板
     *
     * @param $template
     * @param $data
     *
     * @return mixed
     */
    public static function compile_temp($template, $data)
    {
        $variable_pattern = '/\{\{(\w+)\.DATA\}\}/';
        preg_match_all($variable_pattern, $template, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = $match[1];
            if(!isset($data[$key])){
                $value = "";
            }elseif(is_array($data[$key])) {
                $value = $data[$key]['value'];
                if (! empty($data[$key]['color'])) {
                    $value = sprintf('<span style="color: %s">%s</span>', $data[$key]['color'], $value);
                }
            } else {
                $value = $data[$key];
            }
            $template = str_replace('{{'.$key.'.DATA}}', $value, $template);
        }

        //$template = stripslashes($template);//如果有转义，则反转一下
        $modeIf='/\{\{if\s+([\w]+)\.DATA([\>,\<,=]{1,2}[\w]+)\}\}/';
        $modeEndIf='/\{\{\/if\}\}/';
        $modeElse='/\{\{else\}\}/';
        if(preg_match($modeIf,$template) && preg_match($modeEndIf,$template)){ //if{}else{}
            $template = preg_replace($modeIf,"<?php if(\$data['$1']$2){?>",$template);
            $template = preg_replace($modeEndIf,"<?php } ?>",$template);
            if(preg_match($modeElse,$template)){
                $template = preg_replace($modeElse,"<?php }else{ ?>",$template);
            }
            $logPath = Yii::$app->basePath;
            $logPath = rtrim($logPath, '/') . DIRECTORY_SEPARATOR .'runtime/logs/';
            $temp_file = $logPath.'temp_'.time().'.php';
            file_put_contents($temp_file,$template);

            ob_start();
            ob_implicit_flush(false);
            require($temp_file);
            unlink($temp_file);
            return trim(ob_get_clean());
        }
        return $template;
    }

    /**
     * 消息，公告，知识库，发布时间统一检测，如果最后修改时间大于发布时间，则显示最后修改时间，否则显示发布时间
     * @param $modified
     * @param $pub_time
     * @return mixed
     */
    public static function checkPubTime($modified, $pub_time){
        return $modified>$pub_time ? $modified : $pub_time;
    }


    /**
     * 统一返回指定格式的订单号
     * 格式：平台类型+订单类型+年月日时分秒+微秒+随机数+uin后两位
     * @param $plat_from
     * @param $uin
     * @param $type
     * @return string
     */
    public static function setOrderId($plat_from  = 1,$type = 0,$uin = null)
    {
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr($usec,2,4);
        if($uin == null){
            return $plat_from.$type.date('YmdHis').$usec.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);
        }else{
            $suffix = substr($uin,-2);
            return $plat_from.$type.date('YmdHis').$usec.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 2).$suffix;
        }
    }


    /**
     * PHP计算两个时间段是否有交集（边界重叠不算）
     *
     * @param string $beginTime1 开始时间1
     * @param string $endTime1 结束时间1
     * @param string $beginTime2 开始时间2
     * @param string $endTime2 结束时间2
     * @return bool
     */
    public static function is_time_cross($beginTime1, $endTime1, $beginTime2, $endTime2)
    {
        $beginTime1 = is_numeric($beginTime1) ? $beginTime1 : strtotime($beginTime1);
        $beginTime2 = is_numeric($beginTime2) ? $beginTime2 : strtotime($beginTime2);
        $endTime1 = is_numeric($endTime1) ? $endTime1 : strtotime($endTime1);
        $endTime2 = is_numeric($endTime2) ? $endTime2 : strtotime($endTime2);
        $status = $beginTime2 - $beginTime1;
        if ($status > 0) {
            $status2 = $beginTime2 - $endTime1;
            if ($status2 >= 0) {
                return false;
            }else{
                return true;
            }
        } else {
            $status2 = $endTime2 - $beginTime1;
            if ($status2 > 0) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * @param $beginTime1
     * @param $endTime1
     * @param $beginTime2
     * @param $endTime2
     * @return bool
     */
    public static function is_time_valid($beginTime1, $endTime1, $beginTime2, $endTime2)
    {
        $beginTime1 = is_numeric($beginTime1) ? $beginTime1 : strtotime($beginTime1);
        $beginTime2 = is_numeric($beginTime2) ? $beginTime2 : strtotime($beginTime2);
        $endTime1 = is_numeric($endTime1) ? $endTime1 : strtotime($endTime1);
        $endTime2 = is_numeric($endTime2) ? $endTime2 : strtotime($endTime2);

        if($beginTime2-$beginTime1 >= 0 &&  $endTime1-$endTime2 >= 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 生成长ID
     * 时间+4位大写英文字母
     * @return int|string
     */
    public static function generateLongId()
    {
        $str = time();
        for($i = 1; $i <= 4; $i++)
        {
            $str .=rand(0, 9);
        }
        return $str;
    }


    /**
     * 返回格式化字符串
     * @param $str
     * @param array $data
     * @return string
     */
    public static function str_vsprintf($str,$data = array())
    {
        $data = is_array($data) ? $data : [$data];
        foreach($data as &$val){
            if(!is_string($val)){
                $val = json_encode($val);
            }
        }
        return !empty($data) && substr_count($str,'%') <= count($data) ? vsprintf($str,$data) : $str;
    }
}