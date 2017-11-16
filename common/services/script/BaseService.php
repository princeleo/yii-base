<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/19
 * Time: 20:48
 */
namespace app\common\services\script;

use app\common\errors\BaseError;
use app\common\exceptions\ScriptException;
use app\common\vendor\request\HttpClient;
use yii\base\Component;
use app\common\BaseForm;
use Yii;

class BaseService extends Component
{
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
                $form->setAttr($fields);
            }
        }
        //$rs = $form->load(['BaseForm'=>$params]);$rs = $form->validate();pr($form->errors);die;

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
}