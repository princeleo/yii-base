<?php


namespace app\common;

use app\common\errors\BaseError;
use app\common\helpers\ApiUrlHelper;
use app\common\log\Log;
use app\common\vendor\request\HttpClient;
use yii;
use yii\web\Controller;

class BaseController extends Controller
{
    const API_CURL_TIMEOUT = 1; //CURL超时秒数
    const API_URL = null;//API host
    const API_SECRET = null; //请求PAI时密钥
    public $request; //请求对象


    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @param null $params
     * @return mixed|void
     */
    public function afterAction($action, $result, $params = null)
    {
        return true;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool|void
     */
    public function beforeAction($action)
    {
        $this->request = \Yii::$app->request;
        return true;
    }

    // 注销系统自带的实现方法
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    /**
     * 统一参数检查
     * 适应场景ajax
     * @param $params
     * @param array $rules
     * @param array $fields
     * @return bool
     * @throws \app\common\exceptions\ParamsException
     * @example $this->checkForm(['id'=>1,'name'=>'leo','modified'=>time(),'created'=>time()],[[[ 'id','name', 'modified', 'created'], 'required']]);
     */
    public function checkForm($params,$rules = [], $fields = [],$return = false)
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
                $form->setAttr($field);
            }
        }

        if (!$form->load(['BaseForm'=>$params]) || !$form->validate()) {
            $errors = [];
            if(is_array($form->errors)){
                foreach($form->errors as $field => $error){
                    $errors[$field] = $error[0];
                }
            }

            Log::error('checkForm msg =%s | code=%s | data= %s',[$errors,BaseError::PARAMETER_ERR,$params],__METHOD__);
            return self::json($errors,BaseError::PARAMETER_ERR,'',$return ? false : true);
        }
        return $form->toArray();
    }


    /**
     * 添加验证参数
     * @param $params
     * @return int
     */
    private function addSecret($params){
        $params['a'] = Yii::$app->id; //系统平台标识ID
        $params['t'] = time();
        $params['k'] = md5(self::API_SECRET.strtolower(implode(array_reverse($params))));
        return $params;
    }


    /**
     * @param $url
     * @param array $params
     * @param int $timeout
     * @param bool $debug
     * @return mixed|string
     */
    public function getApi($url, $params  = array(),$timeout = self::API_CURL_TIMEOUT,$debug = false)
    {
        if(isset(ApiUrlHelper::$apiMap[$url])){
            $url = ApiUrlHelper::$apiMap[$url];
        }

        if(self::API_SECRET){
            $params = $this->addSecret($params);
        }
        if(strstr($url,'http') === false){
            $url = self::API_URL.$url;
        }
        $result = $respJson = $resp = '';
        $repeat = 3;
        $count = 1;

        while ($count <= $repeat) {
            $return = HttpClient::CallCURLPOST($url, $params, $resp, array(), $timeout); //ADD
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

            $data = array_merge($params,['ur' => $url,'count'=> $count,'result' => $resp]);
            if((YII_ENV == CODE_RUNTIME_LOCAL || YII_ENV == CODE_RUNTIME_DEV) && $debug == true){
                return $resp;
            }
            if ($return === 0) {
                //返回的数据反编码
                if (empty($resp) || ($respJson = json_decode($resp, true)) === false || empty($respJson)) {
                    Log::error('请求数据异常',$data,__METHOD__);
                    return self::json('',BaseError::UNKONWN_ERR,$data,false);
                }
                return $respJson;
            } else {
                Log::error('请求返回异常',$data,__METHOD__);
                return self::json('',BaseError::API_TIMEOUT,$data,false);
            }
            $count++;
            usleep(500000);
        }
        return self::json('',BaseError::UNKONWN_ERR,array_merge($params,[$url]),false);
    }


    /**
     * 统一json格式输出
     * @param $data
     * @param $code
     * @param $msg
     * @throws Exception
     */
    public function json($data , $code = 0, $msg = '',$die = true)
    {
        $code = (int)$code;
        $result = array(
            'code' => $code,
            'msg' => empty($msg) ? BaseError::getError($code) : $msg,
            'data' =>  $data
        );

        if($die != true){
            return json_encode($result);
        }

        exit(json_encode($result));
    }


    /**
     * 标准错误格式输出
     * @param int $code
     * @param string $msg
     * @return string
     */
    public function error($code = 0,$msg = '')
    {
        return $this->json('',$code,$msg);
    }


    /**
     * 标准成功返回函数
     * @param string $data
     * @return string
     */
    public function success($data = '')
    {
        $data = empty($data) ? new \stdClass() : $data;
        return $this->json($data);
    }
}