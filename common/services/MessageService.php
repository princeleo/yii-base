<?php

namespace app\common\services;
use app\common\cache\UserCache;
use app\common\errors\BaseError;
use app\common\exceptions\ParamsException;
use app\common\helpers\BaseHelper;
use app\common\vendor\jpush\src\JPush\Client;
use app\common\vendor\request\HttpClient;
use app\common\vendor\xgpush\Message;
use app\common\vendor\xgpush\MessageIOS;
use app\common\vendor\xgpush\TimeInterval;
use app\common\vendor\xgpush\XingeApp;
use app\models\base\BasePushTask;
use app\models\message\BaseMessage;
use app\models\message\BaseMessageInbox;
use app\models\message\BaseMessageTemplate;
use Yii;
use yii\base\Object;

/**
 * 统一发送消息service
 * Class MessageService
 * @package app\common\services\script
 */
class MessageService {

    const SETTLE_NEW_ORDER_MSG = 'SETTLE_NEW_ORDER_MSG'; //新生成结算单
    const SETTLE_SUBMIT_MSG = 'SETTLE_SUBMIT_MSG';//结算申请提交成功
    const SETTLE_AUDIT_PASS_MSG = 'SETTLE_AUDIT_PASS_MSG';//结算申请审核通过
    const SETTLE_AUDIT_NOT_PASS_MSG = 'SETTLE_AUDIT_NOT_PASS_MSG';//结算审核不通过
    const SETTLE_ABNORMAL_MSG = 'SETTLE_ABNORMAL_MSG'; //异议申请提交成功
    const SETTLE_ABNORMAL_PASS_MSG = 'SETTLE_ABNORMAL_PASS_MSG'; //异议处理完成
    const SETTLE_ABNORMAL_NOT_PASS_MSG = 'SETTLE_ABNORMAL_NOT_PASS_MSG';//异议处理未通过
    const APPLY_CASH_SUBMIT_MSG = 'APPLY_CASH_SUBMIT_MSG'; //提现申请
    const APPLY_CASH_SUCCESS_MSG = 'APPLY_CASH_SUCCESS_MSG'; //提现成功
    const APPLY_CASH_FAIL_MSG = 'APPLY_CASH_FAIL_MSG'; //提现成功
    const MOBILE_CAPTCHA_MSG = 'MOBILE_CAPTCHA_MSG';//手机验证码
    const CUSTOMER_PASS_MESSAGE = 'CUSTOMER_PASS_MESSAGE'; //审核通过通知
    const CUSTOMER_REJECT_MESSAGE = 'CUSTOMER_REJECT_MESSAGE'; //审核不通过通知
    const CUSTOMER_SUBMIT_MESSAGE = 'CUSTOMER_SUBMIT_MESSAGE';//提单通知
    const CUSTOMER_ACCOUNT_SUCCESS = 'CUSTOMER_ACCOUNT_SUCCESS';//商户开户成功短信通知

    const AGENT_CUSTOMER_SUCCESS = 'AGENT_CUSTOMER_SUCCESS';//站内通知-商户开户成功
    const AGENT_CUSTOMER_REJECT = 'AGENT_CUSTOMER_REJECT';//站内通知-开户申请被驳回
    const AGENT_NEW_CUSTOMER = 'AGENT_NEW_CUSTOMER';//站内通知-有新的开户申请
    const AGENT_CUSTOMER_SUBMIT_AGAIN = 'AGENT_CUSTOMER_SUBMIT_AGAIN';//站内通知-重新提单通知


    //####服务商相关
    const AGENT_ACCOUNT_SUCCESS = 'AGENT_ACCOUNT_SUCCESS';//服务商开户成功通知
    const AGENT_ACCOUNT_RESET_PWD = 'AGENT_ACCOUNT_RESET_PWD'; //服务商密码重置
    const PROMOTION_APPLY_SUCCESS = 'PROMOTION_APPLY_SUCCESS';//地推员注册成功
    const PROMOTION_CAPTCHA_MSG = 'PROMOTION_CAPTCHA_MSG';//地推验证码
    const AGENT_EXIT_SUCCESS = 'AGENT_EXIT_SUCCESS';//服务商退出成功-合同终止通知
    const AGENT_ALLOT_CUSTOMER = 'AGENT_ALLOT_CUSTOMER';//BOSS转移商户给服务商-分配的商户通知

    //####商户相关
    const SHOP_SETTLE_REMIND = 'DAILY_SHOP_SETTLE_REMIND';//商户每日结算提醒

    //####地推APP
    const ALLOT_CUSTOMER_PROMOTION = 'ALLOT_CUSTOMER_PROMOTION';//服务商分配商户给地推通知
    const ALLOT_PROMOTION_CUSTOMER = 'ALLOT_PROMOTION_CUSTOMER';//服务商分配地推员给商户通知

    //####短信签名
    const MSM_SIGN_SCTEK = '盛灿科技';
    const MSM_SIGN_ELEPHANT = '大象点餐';
    const AGENT_SERVICE_PHONE = '4001101254';//客服务电话
    const MSG_TYPE_CAPTCHA = 1;//验证码类型
    const MSG_TYPE_MARKET = 2;//营销短信
    const MSG_TYPE_SYS  = 3;//系统通知

    public static function getMsgType()
    {
        return [
            self::SETTLE_NEW_ORDER_MSG => '新生成结算单',
            self::SETTLE_SUBMIT_MSG => '结算处理通知',
            self::SETTLE_AUDIT_PASS_MSG => '审核通过',
            self::SETTLE_AUDIT_NOT_PASS_MSG => '审核不通过',
            self::SETTLE_ABNORMAL_PASS_MSG => '异议处理完成',
            self::SETTLE_ABNORMAL_NOT_PASS_MSG => '异议处理未通过',
            self::APPLY_CASH_SUBMIT_MSG => '提现申请',
            self::APPLY_CASH_SUCCESS_MSG => '提现成功',
            self::APPLY_CASH_FAIL_MSG => '提现失败',
            self::SETTLE_ABNORMAL_MSG=>'异议申请提交成功',
            self::CUSTOMER_PASS_MESSAGE => '审核通过通知',
            self::CUSTOMER_REJECT_MESSAGE => '审核不通过通知',
            self::CUSTOMER_SUBMIT_MESSAGE=>'提单通知'
        ];
    }

    /**
     * 判断是否验证码类型
     */
    public static function isCaptchaMsg($id_key)
    {
        return in_array($id_key,[self::MOBILE_CAPTCHA_MSG,self::PROMOTION_CAPTCHA_MSG]) ? true : false;
    }


    /**
     * 发送站内消息
     * @param $id_key
     * @param $agent_id
     * @param array $params
     * @param int $pub_time
     * @return bool
     */
    public function sendMessage($id_key,$agent_id,$params = array(),$pub_time = 0)
    {
        Yii::info('MessageService |  params |  '.json_encode([$id_key,$agent_id,$params]), __METHOD__);
        if(!is_array($params) || empty($agent_id) || empty($id_key)){
            return false;
        }

        $params = array_merge($params,['id_key' => $id_key, 'agent_id' => $agent_id]);
        $model = (new BaseMessageTemplate())->findOne(['id_key' => $id_key]);
        if(empty($model)){
            Yii::info('MessageService | get template fail', __METHOD__);
            return false;
        }


        $content = BaseHelper::compile_temp($model->content,$params);


        //插入数据
        $save = [
            'title' => $model->title,
            'model_type' => BaseMessage::MESSAGE_TYPE_SYS,
            'content' => $content,
            'template_id' => $model->id,
            'deleted' => BaseMessage::DELETE_DEFAULT,
            'status' => 4,
            'app_id' => Yii::$app->id,
            'write_name' => '系统通知',
            'is_show' => 0,
            'is_import' => BaseMessage::IS_IMPORT,
            'user_id' => 0,
            'pub_time' => empty($pub_time) ? time() : $pub_time
        ];
        $model = new BaseMessage();
        $transaction = \Yii::$app->db->beginTransaction();
        if($model->load(['BaseMessage' => $save]) && $model->save()){
            $message_id = Yii::$app->db->getLastInsertID();
            $inbox_model = new BaseMessageInbox();
            if($inbox_model && $inbox_model->load(['BaseMessageInbox' => [
                    'agent_id' => $agent_id,
                    'message_id' => $message_id,
                    'app_id' => $save['app_id'],
                    'type' => $save['model_type'],
                    'is_pub' => 1,
                    'pub_time' => $save['pub_time']
                ]]) && $inbox_model->save()){
                $transaction->commit();
                //更新缓存
                UserCache::setAgentCache($agent_id);
                return true;
            }
            $transaction->rollBack();
            Yii::info('MessageService SQL exec 1 | fail | '.(empty($model) ? '' : json_encode($model->errors)), __METHOD__);
            return false;
        }
        $transaction->rollBack();
        Yii::info('MessageService SQL exec 2 | fail | '.(empty($model) ? '' : json_encode($model->errors)), __METHOD__);
        return false;
    }


    /**
     * 发送消息
     * @param $push_type 推送类型：1短信|2微信|3APP
     * @param $extend 手机号|open_id|shop_id
     * @param $params 参数
     * @param null $id_key 模板ID
     * @param int $pub_time 发送时间
     * @param int $level 消息级别：1一般，2比较重要，3重要，4非常重要
     * @param int $type 消息业务类型：1提单，2审核，3成功（针对APP消息）
     * @param $title 消息标题(针对APP消息）
     * @return BasePushTask
     * @throws \app\common\exceptions\ParamsException
     * @eg $res = (new MessageService())->addTask(1,'18927498947',['verify_code' => 398432],'VERIFY_CODE_MSG');
     */
    public function addTask($push_type,$extend,$params,$id_key,$level = BasePushTask::MSG_LEVEL_1,$title = '',$type = BasePushTask::MSG_TYPE_1,$pub_time = 0,$client = 'dt')
    {
        Yii::error('MessageService | message task params | params='.json_encode($params),__METHOD__);
        if(empty($push_type) || empty($extend) || empty($params) || !is_array($params)){
            throw new ParamsException(BaseError::PARAMETER_ERR);
        }

        //判断类型
        if(!isset(BasePushTask::getPushType()[$push_type])){
            throw new ParamsException(BaseError::MESSAGE_PUSH_TYPE_ERR);
        }

        //检查模板ID是否有效
        $temp = (new BaseMessageTemplate())->find()->where(['id_key' => $id_key])->one();
        if(empty($temp)){
            Yii::error('MessageService | message template not find | id_key='.$id_key,__METHOD__);
            throw new ParamsException(BaseError::MESSAGE_TEMP_ID_NOT_FIND);
        }
        $title = !empty($title) ? $title : $temp['title'];
        $content = BaseHelper::compile_temp($temp['content'],$params);
        $temp_config = empty($temp['data_config']) ? [] : json_decode($temp['data_config'],true);
        $temp_id = empty($temp_config['temp_id']) ? '' : $temp_config['temp_id'];

        //保存task任务
        $save = [
            'task_key' => $this->setTaskKey($extend),
            'push_type' => $push_type,
            'temp_id' => $id_key,
            'content' => $content,
            'extend' => (string)$extend,
            'level' => empty($level) ? 1 : $level,
            'type' => empty($type) ? 1 : $type,
            'title' => $title,
            'star_time' => intval($pub_time),
        ];
        $push_model = new BasePushTask();
        if(!$push_model->load(['BasePushTask' => $save]) || !$push_model->save()){
            Yii::error('MessageService | message task save fail | error='.json_encode($push_model->errors).' | params='.json_encode($save),__METHOD__);
            throw new ParamsException(BaseError::SVR_ERR);
        }

        //消息处理
        if($pub_time == 0){
            $result = '';
            switch($push_type){
                case BasePushTask::PUSH_TYPE_SMS://发送短信
                    if(empty($temp_id)){
                        Yii::error('MessageService | message temp_id not find | params='.json_encode($params),__METHOD__);
                        throw new ParamsException(BaseError::MESSAGE_TEMP_ID_NOT_FIND);
                    }

                    $params_arr = [];
                    foreach($temp_config as $key=>$val){
                        if($key != 'temp_id' && (!empty($params[$key]) || !empty($val))){
                            $params_arr[$key] = !empty($params[$key]) ? $params[$key] : $val;
                        }
                    }
                    Yii::error(json_encode($temp_config).' | params ='.json_encode($params).' | params_arr ='.json_encode($params_arr),__METHOD__);
                    $result = $this->sendSmsTemp($extend,$temp_id,$params_arr,empty($params['sms_sign']) ? '' : $params['sms_sign'],self::isCaptchaMsg($id_key) ? self::MSG_TYPE_CAPTCHA : self::MSG_TYPE_SYS);
                    break;

                case BasePushTask::PUSH_TYPE_WX://微信推送
                    break;

                case BasePushTask::PUSH_TYPE_APP://APP推送
                    $app_params = [
                        'type' => $type,
                        'value' => $push_model['id'],
                        'extra' => new Object()
                    ];

                    if($client == 'dt'){
                        $xpush = $this->pushSingleAccount($title,$content,$extend,$app_params);
                        $jpush = $this->pushSingleAccountIOS($title,$content,$extend,$app_params);
                        if($xpush == true || $jpush == true){
                            $result = true;
                        }else{
                            $result = false;
                        }
                    }else{
                        $jpush = $this->jpushSingleAccount($title,$content,$extend,$app_params);
                        if($jpush == true){
                            $result = true;
                        }else{
                            $result = false;
                        }
                    }
                    break;
            }

            $params = [
                'count' => $push_model['count']+1,
                'error' => is_string($result) ? $result : '',
                'state' => $result === true ? BasePushTask::PUSH_STATE_DONE : BasePushTask::PUSH_STATE_FAIL,
            ];
            if(!$push_model->load(['BasePushTask' => $params]) || !$push_model->save()){
                Yii::error('MessageService | message push fail | params='.json_encode($params),__METHOD__);
                throw new ParamsException(BaseError::SVR_ERR);
            }
        }

        return $push_model;
    }


    /**
     * 发送安卓APP消息——信鸽推送
     * @param $title
     * @param $content
     * @param $account
     * @param $custom
     * @return mixed
     */
    protected  function pushSingleAccount($title,$content,$account,$custom = [])
    {
        $app_conf = Yii::$app->params['app_xpush_conf'];
        $push = new XingeApp($app_conf['access_id'], $app_conf['secret_key']);
        $mess = new Message();
        $mess->setExpireTime(86400);
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setCustom($custom);
        $mess->setType(Message::TYPE_MESSAGE);
        $ret = $push->PushSingleAccount(0, $app_conf['account_prefix'].$account, $mess);

        $params = [
            'title' => $title,
            'account' => $app_conf['account_prefix'].$account,
            'custom' => $custom
        ];
        if(is_array($ret) && $ret['ret_code'] == 0){
            $ret = true;
        }else{
            Yii::error('MessageService error | params ='.json_encode($params).' | response ='.json_encode($ret),__METHOD__);
            $ret = false;
        }
        return ($ret);
    }

    /**
     * 发送IOS APP消息——信鸽推送
     * @param $title
     * @param $content
     * @param $account
     * @param array $custom
     * @return mixed
     */
    protected function pushSingleAccountIOS($title,$content,$account,$custom = [])
    {
        $app_conf = Yii::$app->params['app_jpush_conf'];
        $client = new Client($app_conf['app_key'],$app_conf['secret_key']);
        $push_payload = $client->push()
            ->setPlatform('ios')
            ->addAlias($app_conf['account_prefix'].$account)
            ->iosNotification($title, [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => $custom
            ]);
        $params = [
            'title' => $title,
            'account' => $app_conf['account_prefix'].$account,
            'custom' => $custom
        ];
        try {
            $response = $push_payload->send();
            Yii::info('MessageService | response ='.json_encode($response),__METHOD__);
        } catch (\app\common\vendor\jpush\src\JPush\Exceptions\APIConnectionException $e) {
            Yii::error('MessageService error | params ='.json_encode($params).' | response ='.json_encode($e),__METHOD__);
            return false;
        } catch (\app\common\vendor\jpush\src\JPush\Exceptions\APIRequestException $e) {
            Yii::error('MessageService error | params ='.json_encode($params).' | response ='.json_encode($e),__METHOD__);
            return false;
        }

        return true;
    }


    /**
     * 极光推送
     * @param $title
     * @param $content
     * @param $account
     * @param array $custom
     * * @return mixed
     */
    public   function jpushSingleAccount($title,$content,$account,$custom = [],$conf = [])
    {
        $app_conf = empty($conf) ? Yii::$app->params['shop_app_jpush_config'] : $conf;
        $app = new Client($app_conf['app_key'],$app_conf['secret_key']);
        $push_payload = $app->push()
            ->setPlatform(['ios','android'])
            ->addAlias($account)
            ->setNotificationAlert($title)
            ->androidNotification($content, array(
                'title' => $title,
                'build_id' => 2,
                'extras' => $custom,
            ))
            ->iosNotification($title, [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => $custom
            ]);
        $params = [
            'title' => $title,
            'account' => $account,
            'custom' => $custom
        ];
        try {
            Yii::info('MessageService | $params ='.json_encode($params),__METHOD__);
            $response = $push_payload->send();
        } catch (\app\common\vendor\jpush\src\JPush\Exceptions\APIConnectionException $e) {
            Yii::error('MessageService error | params ='.json_encode($params).' | response ='.json_encode($e),__METHOD__);
            return false;
        } catch (\app\common\vendor\jpush\src\JPush\Exceptions\APIRequestException $e) {
            Yii::error('MessageService error | params ='.json_encode($params).' | response ='.json_encode($e),__METHOD__);
            return false;
        }

        return true;
    }


    /**
     * 发送手机模板类短信
     * @param $mobile
     * @param $temp_id
     * @param $params
     * @param string $sign
     * @return bool|string
     */
    protected function sendSmsTemp($mobile,$temp_id,$params,$sign = self::MSM_SIGN_SCTEK,$type = self::MSG_TYPE_SYS)
    {
        $data = array(
            'mobile' => (string)$mobile,
            //'content' => $content,
            'sign' => empty($sign) ? self::MSM_SIGN_SCTEK : $sign,
            'platform' => 23, //BOSS平台
            "temp_id" => $temp_id,
            "temp_params" => json_encode($params),
            'type' => $type
        );
        Yii::info('MessageService | send sms | params:'.json_encode($data),__METHOD__);
        $send_server = YII_ENV != CODE_RUNTIME_ONLINE ? 'http://betasms.snsshop.net/v1/sms-api/sms-send' :  'http://msg.api.nexto2o.com/v1/sms-api/sms-send';
        $return = HttpClient::CallCURLPOST($send_server, json_encode($data), $resp, array(), 2);
        $resp_data = is_array($resp) ? $resp : json_decode($resp,true);
        if($return !== BaseError::SUCC || !$resp_data || $resp_data['errcode'] != 20000){
            Yii::error('MessageService | send sms fail | params:'.json_encode($data).' | resp ='.json_encode($resp_data),__METHOD__);
            return empty($resp_data['errmsg']) ? 'false' : json_encode($resp_data);
        }

        return true;
    }


    /**
     * 发送手机短信
     * @param $mobile
     * @param $content
     * $params $sign 短信签名
     * @return bool|string
     */
    protected function sendSMS($mobile,$content,$sign = self::MSM_SIGN_SCTEK,$type = self::MSG_TYPE_SYS)
    {
        $data = array(
            'mobile' => (string)$mobile,
            'content' => $content,
            'sign' => empty($sign) ? self::MSM_SIGN_SCTEK : $sign,
            'platform' => 23,
            'type' => $type
        );
        $send_server = YII_ENV != CODE_RUNTIME_ONLINE ? 'http://betasms.snsshop.net/v1/sms-api/sms-send' :  'http://msg.api.nexto2o.com/v1/sms-api/sms-send';
        $return = HttpClient::CallCURLPOST($send_server, json_encode($data), $resp, array(), 2);
        $resp_data = is_array($resp) ? $resp : json_decode($resp,true);
        if($return !== BaseError::SUCC || !$resp_data || $resp_data['errcode'] != 20000){
            Yii::error('MessageService | send sms fail | params:'.json_encode(array_merge($data,array('toUin'=>$mobile,'content'=>$content))).' | resp ='.json_encode($resp_data),__METHOD__);
            return empty($resp_data['msg']) ? 'false' : json_encode($resp_data);
        }

        return true;
    }


    /**
     * 发送邮件消息
     * @param $title
     * @param $toUser
     * @param $ccUser
     * @param $content
     *
     * @return int
     */
    public function send_email($title,$content,$toUser,$ccUser = array())
    {
        if(empty($title) || !is_array($toUser) || empty($toUser['email']) || empty($toUser['name'])){
            return false;
        }

        $cc_mail = $cc_name = "";
        if(is_array($ccUser)){
            foreach($ccUser as $cc){
                $cc_mail .= $cc['email'].';';
                $cc_name .= $cc['name'].';';
            }
            $cc_mail = trim($cc_mail,';');
            $cc_name = trim($cc_name,';');
        }

        $send_server = 'http://email.vikduo.com/mail.php';
        $temp_server = 'http://email.vikduo.com/mailTemplate.php';//邮件模板URL 接口地址
        $info_server = 'http://email.vikduo.com/mailInfoGet.php';//获取邮件详情
        $mail_user = 'weikeduo@vikduo.com';
        $mail_pwd = 'CxFhKS507ao=123';
        $mail_user_name = 'BOSS项目团队';
        $postData = array(
            "rcpt"=> $toUser['email'],
            "rcpt_name"=> $toUser['name'],
            "cc"=> $cc_mail,
            "cc_name"=> $cc_name,
            "bcc"=> "zourf@sctek.com",
            "bcc_name"=> "leo.zou",
            "subject"=> $title,
            "content"=> is_array($content) ? json_encode($content) : $content,
            'mail_user' => $mail_user,
            'mail_user_name' => $mail_user_name,
            'mail_password' => $mail_pwd,
            "type" => "json"
        );
        $return = HttpClient::CallCURLPOST($send_server, http_build_query($postData), $resp, array(), 3);
        $resp_data = is_array($resp) ? $resp : json_decode($resp,true);
        if($return !== BaseError::SUCC || !$resp_data || $resp_data['code'] != 200){
            //$this->log->error('Push_service', 'send email fail', array('resp_data' => $resp_data,'toUser'=>$toUser,'content'=>$content));
            return empty($resp_data['msg']) ? 'false' : json_encode($resp_data);
        }

        return true;
    }


    /**
     * 生成推送唯一KEY
     */
    protected function setTaskKey($key)
    {
        $usec = strlen($key) > 2 ? substr($key,2,4) : $key;
        $usec = is_numeric($usec) ? $usec : mt_rand(1,10000);
        return date('YmdHis').$usec.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);
    }
}