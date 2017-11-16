<?php


namespace app\modules\tools\controllers;


use app\common\cache\UserCache;
use app\common\exceptions\ApiException;
use app\common\errors\BaseError;
use app\common\helpers\ApiUrlHelper;
use app\common\helpers\BaseHelper;
use app\common\filters\AuthFilters;
use app\common\cache\Session;
use app\common\log\ActionLog;
use app\common\ResultModel;
use app\common\BaseForm;
use app\common\vendor\request\HttpClient;
use app\models\base\AccessLog;
use yii\base\Exception;
use yii\web\Controller;
use Yii;

class BaseController extends Controller{
    const CURL_TIMEOUT = 3; //CURL超时秒数
    public $request; //请求对象
    public $resultModel;
    public $whiteList = []; //权限白名单
    public $layout = 'layout';


    public $_departments = null;//部门
    public $_permissions = null;//权限
    public $_roles = null;
    public $_user;//用户


    public function init()
    {
        $this->resultModel = new ResultModel();
    }

    // 注销系统自带的实现方法
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    /**
     * @param $model
     * @param $id
     * @param $params
     * @return mixed
     * @throws \app\common\exceptions\ApiException
     */
    protected  function baseSave($model,$params,$id = 0)
    {
        if($id > 0) {
            $model = $model->findOne($id);
            $params['modified'] = time();
        }else{
            $params['created'] = time();
            $params['modified'] = time();
        }
        if ($model->load([$model->formName()=>$params]) && $res = $model->save()) {
            return $res;
        } else {
            throw new ApiException(BaseError::SVR_ERR,$params,!empty($model->errors) ?  $model->errors : $model->getLastSql());

        }
    }


    /**
     * @param $model
     * @param $params
     */
    protected  function baseSearch($model,$params)
    {
        $params = array_merge([
            'count' => 10, //page_size
            'page' => 1 //当前页
        ],$params);
    }


    /**
     * @param $model
     * @param $id
     * @return mixed
     * @throws \app\common\exceptions\ApiException
     */
    protected  function baseDelete($model,$id)
    {
        $model = $model->findOne($id);
        if ($res = $model->delete()) {
            return $res;
        } else {
            throw new ApiException(BaseError::SVR_ERR,['id'=>$id],!empty($model->errors) ?  $model->errors : $model->getLastSql());
        }
    }





    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @param null $params
     * @return mixed|void
     */
    public function afterAction($action, $result, $params = null)
    {
        return $result;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \app\common\exceptions\AuthException
     */
    final public function beforeAction($action)
    {
        return true;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed|string
     * @throws \app\common\exceptions\ApiException
     */
    public function getApi($url, $params  = array(),$debug = false)
    {
        $query = '';
        if(strstr($url,'?') !== false){
            $url = explode('?',$url);
            $url = $url[0];
            $query = $url[1];
        }
        if(isset(ApiUrlHelper::$apiMap[$url])){
            $url = ApiUrlHelper::$apiMap[$url];
        }
        $url = !empty($query) ? $url.'?'.$query : $url;

        //设置分页参数
        $pagetion =  [];
        if(Yii::$app->request->get('page')){
            $pagetion['page'] = Yii::$app->request->get('page');
        }
        if(Yii::$app->request->get('per-page')){
            $pagetion['per-page'] = Yii::$app->request->get('per-page');
        }
        if(!empty($pagetion)){
            $url  =  strstr($url,'?') === false ? $url.'?'.http_build_query($pagetion) : $url.'&'.http_build_query($pagetion);
        }

        $params = is_array($params) ? $params : [$params];
        $result = $respJson = $resp = '';
        $apiStartTime = microtime(true);
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

            if((YII_ENV == CODE_RUNTIME_LOCAL || YII_ENV == CODE_RUNTIME_DEV) && $debug == true){
                return $resp;
            }
            if ($return === 0) {
                //返回的数据反编码
                if (empty($resp) || ($respJson = json_decode($resp, true)) === false || empty($respJson)) {
                    return self::result('',BaseError::UNKONWN_ERR,$params,false);
                }
                return $respJson;
            } else {
                return self::result('',BaseError::API_TIMEOUT,$params,false);
            }
            $count++;
            usleep(500000);
        }
        return self::result('',BaseError::UNKONWN_ERR,$params,false);
    }


    /**
     * 统一参数检查
     * @param $params
     * @param array $rules
     * @param array $fields
     * @return bool
     * @throws \app\common\exceptions\ApiException
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
                $form->setAttr($field);
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
                throw new \app\common\exceptions\FormException(BaseError::PARAMETER_ERR,$params,$errors);
            }
        }catch (Exception $ex){
            throw new \app\common\exceptions\FormException(BaseError::UNKONWN_ERR,$fields,$ex->getMessage());
        }

        return $form->toArray();
    }


    /**
     * 统一json格式输出
     * @param $data
     * @param $code
     * @param $msg
     * @throws Exception
     */
    public static  function result($data = array(), $code = 0, $msg = '',$die = true)
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

        if($die != true){
            return json_encode($result);
        }

        //注意：如果在此之前有输出，会出现空白
        header('Content-type:text/json');
        exit(json_encode($result));
    }


    /**
     * @param $key
     */
    public function getCache($key){

    }


    /**
     * @param $key
     * @param $data
     * @param int $expire
     */
    public function setCache($key,$data,$expire = 300)
    {

    }


    /**
     * @param $key
     */
    public function delCache($key)
    {

    }

    /**
     *  去除空
     * @param array $params
     * @return array $params
     */
    public function RemoveEmpty($params){
        foreach ($params as $k => $v) {
            if (empty($v)) {
                unset($params[$k]);
            }
        }
        return $params;
    }
}