<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/19
 * Time: 15:23
 */
namespace app\modules\script\controllers;
use app\common\BaseForm;
use app\common\errors\BaseError;
use app\common\exceptions\FormException;
use app\common\exceptions\ScriptException;
use app\common\vendor\request\HttpClient;
use yii\console\Controller;
use Yii;

/**
 * Class BaseController
 * 脚本基类
 * @package app\modules\script\controllers
 */
class BaseController extends Controller {
    /**
     * 业务日志key
     * @var string
     */
    protected $businessLogKey = '';
    public $modelClass = '';

    public function init()
    {
        parent::init();
    }

    /**
     * 设置业务日志key
     */
    protected function setBusinessLogKey()
    {
        $this->businessLogKey = $this->getRoute().'_'.date('YmdHis');
    }



    /**
     * 超时日志
     */
    const CURL_TIMEOUT = 5;

    /**
     * @param $url
     * @param array $params
     * @return mixed|string
     * @throws ScriptException
     */
    public function httpCurlPost($url, $params  = array())
    {
        Yii::info('start | exec time['.microtime(true).'] | url['.$url.'] | params['.json_encode($params).']', __METHOD__);
        $result = $respJson = $resp = '';
        $repeat = 3;
        $count = 1;
        while ($count <= $repeat) {
            $return = HttpClient::CallCURLPOST($url, $params, $resp, array(), self::CURL_TIMEOUT); //ADD
            switch ($return) {
                case  0:
                    $result = 'ok';
                    break;
                case -1:
                    $result = 'contect error';
                    break;
                case -2:
                    $result = 'responseCode not 200';
                    break;
            }

            if ($return === 0) {
                //返回的数据反编码
                if (empty($resp) || ($respJson = json_decode($resp, true)) === false || empty($respJson)) {
                    Yii::info('loop['.$count.'] | fail| exec time['.microtime(true).'] | url['.$url.'] | params['.json_encode($params).'] | resp['.$resp.']', __METHOD__);
                    throw new ScriptException(BaseError::UNKONWN_ERR, $params);
                }
                Yii::info('loop['.$count.'] | success | exec time['.microtime(true).'] | url['.$url.'] | params['.json_encode($params).'] | resp['.$resp.']', __METHOD__);
                return $respJson;
            }
            Yii::info('loop['.$count.'] | fail| exec time['.microtime(true).'] | url['.$url.'] | params['.json_encode($params).'] | result['.$result.']', __METHOD__);
            $count++;
            usleep(500000);
        }

        Yii::info('loop['.$count.'] | fail| exec time['.microtime(true).'] | url['.$url.'] | params['.json_encode($params).'] | unknown error', __METHOD__);
        throw new ScriptException(BaseError::UNKONWN_ERR,$params);
    }



    /**
     * 统一参数检查
     * @param $params
     * @param array $rules
     * @param array $fields
     * @return bool
     * @throws \app\common\exceptions\FormException
     *
     * @example $this->checkForm(['id'=>1,'name'=>'leo','modified'=>time(),'created'=>time()],[[[ 'id','name', 'modified', 'created'], 'required']]);
     */
    public function checkForm($params,$rules = [], $fields = [])
    {
        $form = new BaseForm($rules);
        if(is_array($fields)){
            if(empty($fields)){
                $fields = array();
                foreach($rules as $rule){
                    $fields = array_merge($fields,$rule[0]);
                }
            }
            foreach($fields as $field){
                $form->$field = null;
            }
        }

        try{
            if (!$form->load(['BaseForm'=>$params]) || !$form->validate()) {
                $errors = [];
                if(is_array($form->errors)){
                    foreach($form->errors as $field => $error){
                        $errors[$field] = $error[0];
                    }
                }
                throw new FormException(BaseError::PARAMETER_ERR,$params,$errors);
            }
        }catch (FormException $ex){
            throw new FormException(BaseError::UNKONWN_ERR,$fields,$ex->getMessage());
        }
        return true;
    }

    /**
     * 格式化日期
     * @param $startDate 日期
     * @param bool $toTime  是否转为时间戳
     * @param null $fun
     * @return false|int|string
     * @throws ScriptException
     */
    protected function formatDate($startDate, $fun=null, $toTime=true)
    {
        if (!is_null($startDate) && is_int($startDate)) {
            $startDate = date('Y-m-d', $startDate);
        }

        if ((!is_null($startDate) && !strtotime($startDate))) {
            Yii::info('end | exec time['.microtime(true).'] | '.$fun.' | params error | params['.json_encode(func_get_args()).']', __METHOD__);
            throw new ScriptException('10001', [], $fun.' | params error | params['.json_encode(func_get_args()));
        }
        $startDate = is_null($startDate) ? date('Y-m-d', strtotime('-1 days')) : date('Y-m-d', strtotime($startDate));
        return $toTime ? strtotime($startDate) : $startDate;
    }

    /**
     * 格式化分页
     * @param int $pageIndex
     * @param int $pageSize
     * @return array
     */
    protected function formatPagination($pageIndex=1, $pageSize=500)
    {
        return [
            'page' => (empty($pageIndex) || !is_int($pageIndex) || $pageIndex <=0) ? 0 : $pageIndex,
            'pageSize' => (empty($pageSize) || !is_int($pageSize) || $pageSize <=0) ? 500 : $pageSize,
        ];
    }

    /**
     * 格式化日期区间
     * @param $startDate
     * @param $endDate
     * @param bool $toTime
     * @param null $fun
     * @param array $default
     * @return mixed
     * @throws ScriptException
     */
    protected function formatDateArea($startDate, $endDate, $fun=null, $default=[], $toTime=true)
    {
        if (!is_null($startDate) && is_int($startDate)) {
            $startDate = date('Y-m-d', $startDate);
        }
        if (!is_null($endDate) && is_int($endDate)) {
            $endDate = date('Y-m-d', $endDate);
        }

        if ((!is_null($startDate) && !strtotime($startDate)) ||
            (!is_null($endDate) && !strtotime($endDate))
        ) {
            Yii::info('end | exec time['.microtime(true).'] | '.$fun.' | params error | params['.json_encode(func_get_args()).']', __METHOD__);
            throw new ScriptException('10001', [], $fun.' | params error | params['.json_encode(func_get_args()));
        }
        if (is_null($startDate)) {
            if (empty($default['startTime'])) {
                $startDate = date('Y-m-d', strtotime('-1 days'));
                $ret['startTime'] = strtotime($startDate); //开始时间
            } else {
                $ret['startTime'] = $default['startTime']; //开始时间
            }
            if (empty($default['endTime'])) {
                $ret['endTime'] = $ret['startTime'] + 86400;      //结束时间
            } else {
                $ret['endTime'] = $default['endTime'];      //结束时间
            }
        } else {
            $startDate = date('Y-m-d', strtotime($startDate));
            $ret['startTime'] = strtotime($startDate); //开始时间
            if (is_null($endDate)) {
                if (empty($default['endTime'])) {
                    $ret['endTime'] = $ret['startTime'] + 86400;      //结束时间
                } else {
                    $ret['endTime'] = $default['endTime'];      //结束时间
                }
            } else {
                $endDate = date('Y-m-d', strtotime($endDate));
                $ret['endTime'] = strtotime($endDate) + 86400;      //结束时间
                if ($ret['endTime'] < $ret['startTime']) {
                    Yii::info('end | exec time['.microtime(true).'] | '.$fun.' | params error | params['.json_encode(func_get_args()).']', __METHOD__);
                    throw new ScriptException('10002', [], $fun.' | params error | params['.json_encode(func_get_args()));
                }
            }
        }
        if (!$toTime) {
            $ret['startTime'] = date('Y-m-d', $ret['startTime']);
            $ret['endTime'] = date('Y-m-d', $ret['endDate']);
        }
        return $ret;
    }


    /**
     * 正时补差
     * @param $EndTime
     * @param $table
     * @param $object
     * @param $action
     * @return string
     */
    protected function _Correction_Time($EndTime, $table, $object, $action = 'actionIndex')
    {
        $DataTime = $StartTime = $table->find()->select('date')->orderBy('date desc')->asArray()->one();

        if (!empty($StartTime)) {
            $StartTime = strtotime($StartTime['date']) + $object->TimeInterval;
        } else {
            $StartTime = $EndTime - $object->TimeInterval;
        }
        $difference = $EndTime - $StartTime;

        if ($difference == $object->TimeInterval) {
            return $EndTime;
        }else if ($difference < $object->TimeInterval){
            Yii::info('__Start：(Table:'.$table::tableName().')操作频繁结束统计!', 'Script');
            exit('Frequent operation ---- end');
        }

        $difference = $difference/$object->TimeInterval;
        $difference = (int)$difference;

        if ($difference == 1) {
            return $StartTime+$object->TimeInterval;
        }
        for ($i = 1; $i <= $difference; $i++) {
            $EndTime = $StartTime+($i*$object->TimeInterval);
            $object->$action($EndTime);
        }
        exit("---- Execute ".($i-1)." frequency");
    }

    /**
     * 正时补差
     * @param $StartTime
     * @param $EndTime
     * @param $object
     * @param $action
     * @param $TimeInterval
     * @return string
     */
    protected function _EverySingleDay($StartTime, $EndTime, $object, $action = 'actionIndex', $TimeInterval = 86400)
    {
        $difference = (int)(($EndTime - $StartTime)/$TimeInterval);
        for ($i = 1; $i <= $difference; $i++) {
            $EndTime = $StartTime+($i*$TimeInterval);
            $object->$action($EndTime);
            echo date("Y-m-d H:i:s", ($EndTime-$TimeInterval))." is ok! \n";
        }
        exit("---- Execute ".($i-1)." frequency");
    }

    /**
     *  查看是否重复插入
     * @param $StartTime
     * @param $table
     * @return bool
     */
    protected function _dataIsRepeated($StartTime, $table)
    {
        $data = $table::find()->where("date = str_to_date('$StartTime', '%Y-%m-%d %H:%i:%s')")->one();
        if ($data) {
            return false;
        }
        return true;
    }
}