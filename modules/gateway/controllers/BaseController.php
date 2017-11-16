<?php


namespace app\modules\gateway\controllers;

use app\common\BaseForm;
use app\common\errors\BaseError;
use app\common\log\Log;
use Yii;

class BaseController extends \app\common\BaseController{
    const CHECK_SECRET = TRUE; //检查加密开关
    public $params_post;
    public $debug = false;
    public $app_secret = [];

    public function init()
    {
        parent::init();
        $this->paramsPost = !is_array(json_decode(Yii::$app->request->rawBody,true)) ? Yii::$app->request->post() : json_decode(Yii::$app->request->rawBody,true);
        $this->debug = isset($this->paramsPost['debug']) ? true : false;
        if(!self::CHECK_SECRET) return;

        //*******加密验证：必须通过加密机制a=应用标识,k=加密串,t=请求时间**************//
        $a = empty($this->paramsPost['a']) ? '' : $this->paramsPost['a'];
        $k = empty($this->paramsPost['k']) ? '' : $this->paramsPost['k'];
        $t = empty($this->paramsPost['t']) ? '' : $this->paramsPost['t'];
        $secret = empty($this->app_secret[$a]) ? '' : $this->app_secret[$a];

        unset($this->paramsPost['k']);
        $key = md5($secret.strtolower(implode(array_reverse($this->paramsPost))));
        if(empty($a) || empty(Yii::$app->params['appSecret'][$a])){
            $error = '密钥获取失败';
        }elseif(empty($k) || $k != $key){
            $error = '加密验证失败';
        }elseif(YII_ENV != CODE_RUNTIME_LOCAL && (empty($t) || intval($t) + 2*60 < time())){
            $error = '请求已失效';
        }

        if(!empty($error)){
            Log::error($error,$this->paramsPost,__METHOD__);
            $key = in_array(YII_ENV,[CODE_RUNTIME_LOCAL,CODE_RUNTIME_TEST]) ? $key : '';
            self::json($this->debug ? array_merge($this->paramsPost,['k' => $key,'t' => time()]) : '',BaseError::INVALID_REQUEST,$this->debug ? $error : '');
        }
    }
}
