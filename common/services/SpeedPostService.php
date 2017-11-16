<?php

namespace app\common\services;


use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\common\vendor\api\gateWayApi;
use app\common\vendor\request\HttpClient;
use app\models\base\BasePushTask;
use app\models\shop\Customer;
use app\models\shop\CustomerAudit;
use app\models\shop\CustomerPaymentSetting;
use app\models\shop\CustomerSpeedpostCallback;
use app\modules\api\controllers\BaseController;
use Yii;
use yii\base\Component;

class SpeedPostService extends Component
{
    /**
     * 商户开启费率更新
     */
    public function ShopRateUpdate($params)
    {
        $searchParams = [
            'type' => $params->type,
            'mch_id' => $params->mch_id,
            'trade_type' => $params->trade_type,
            'calc_rate' => $params->calc_rate * 10,
        ];

        $paySetting = CustomerPaymentSetting::findOne(['speedpos_id'=>$searchParams['mch_id']]);
        if(empty($paySetting)){
            //旧数据，speedpos_id在customer表中
            $customer = Customer::findOne(['speedpos_id'=>$searchParams['mch_id']]);
            $paySetting = CustomerPaymentSetting::findOne(['customer_id'=>$customer['id']]);
            if(empty($paySetting)){
                $this->result('',BaseError::AGENT_NOT_EXISTS,'查找不到此商户数据');
            }
        }
        $time = time();
        $transaction = Yii::$app->db->beginTransaction();

        Yii::error($searchParams, 'paysettingUpdate_params');
        $settingResult = CustomerPaymentSetting::updateAll(['rate'=>$searchParams['calc_rate']],['speedpos_id'=>$searchParams['mch_id']]);
        if($settingResult < 0){
            $transaction->rollBack();
            return false;
        }
        //对接修改费率接口
        $customerArray = (new Customer())->customerArray($paySetting->customer_id, true);
        $result = (new gateWayApi())->syncAccount($customerArray);
        $resp =  array();
        $res = HttpClient::CallCURLPOST(GATEWAY_BOSS . 'shop/update-payment-setting', $result,$resp,array());
        Yii::error($resp, 'update_payment_setting');
        $resp =  is_string($resp) ? json_decode($resp,true) : $resp;
        if (!is_array($resp) || $resp['code'] != 0) {
            $transaction->rollBack();
            return $resp;
        }

        $transaction->commit();
        return true;
    }


    /**
     * 商户开启状态同步更新
     */
    public function ShopStatusSync($params)
    {
        $searchParams = [
            'type' => $params->type,
            'mch_id' => $params->mch_id,
            'ispass' => isset($params->ispass) ? $params->ispass : 0,
            'audit_remark' => $params->audit_remark,
        ];

        $paySetting = CustomerPaymentSetting::findOne(['speedpos_id'=>$searchParams['mch_id']]);
        if(empty($paySetting)){
            //旧数据，speedpos_id在customer表中
            $customer = Customer::findOne(['speedpos_id'=>$searchParams['mch_id']]);
            $paySetting = CustomerPaymentSetting::findOne(['customer_id'=>$customer['id']]);
            if(empty($paySetting)){
                $this->result('',BaseError::AGENT_NOT_EXISTS,'查找不到此商户数据');
            }
        }
        $time = time();
        $transaction = Yii::$app->db->beginTransaction();

        $customerAudit = new CustomerAudit();
        if ($searchParams['ispass'] == 1) {
            //操作日志详情
            $audit['customer_id'] = $paySetting->customer_id;
            $audit['type'] = CustomerAudit::BOSS_AUDIT_STATUS;
            $audit['audit_status'] = CustomerAudit::CUSTOMER_AUDIT_CHECK;
            $audit['status'] = CustomerAudit::AUDIT_STATUS_SUC;
            $audit['title'] = '银行审核结果：通过';
            $audit['desc'] = $searchParams['audit_remark'];
            $customerAudit->auditCreate($audit);

            $audit['customer_id'] = $paySetting->customer_id;
            $audit['type'] = CustomerAudit::AGENT_AUDIT_STATUS;
            $audit['audit_status'] = CustomerAudit::AGENT_CUSTOMER_REVIEW_OPEN;
            $audit['status'] = CustomerAudit::AUDIT_STATUS_SUC;
            $audit['title'] = '银行审核结果：通过';
            $audit['desc'] = $searchParams['audit_remark'];
            $customerAudit->auditCreate($audit);

        } else {
            $customer = (new Customer())->findOneModel($paySetting->customer_id);
            //审核不通过-更新商家表信息
            $customer->status = Customer::CUSTOMER_AGENT_STATUS_REJECT;
            $customer->review_status = Customer::CUSTOMER_BOSS_REVIEW_REJECT;
            $customer->reject_desc = $searchParams['audit_remark'];
            $customer->modified = $time;
            if (!$customer->save()) {
                throw new ApiException(BaseError::SAVE_ERROR);
            }

            //审核不通过通知APP
            if (!(new MessageService())->addTask(BasePushTask::PUSH_TYPE_APP, $customer->promotion->id, ['name' => $customer->name, 'reject_desc' => $searchParams['audit_remark']], MessageService::CUSTOMER_REJECT_MESSAGE,BasePushTask::MSG_LEVEL_3,"",BasePushTask::MSG_TYPE_2)) {
                throw new ApiException(BaseError::SAVE_ERROR);
            }

            /**自动发送消息*/
            (new MessageService())->sendMessage(MessageService::AGENT_CUSTOMER_REJECT,$customer->agent_id,array(
                'agent_name'=>$customer->agentBase->agent_name,
                'agent_time' => date('Y年m月d日'),
                'desc'=>$searchParams['audit_remark']
            ));

            //删除之前提交进件资料的操作日志
            if((new CustomerAudit())->find()->where(['customer_id'=>$paySetting->customer_id,'agent_audit_status'=>CustomerAudit::AGENT_CUSTOMER_OPEN_PAY])->one()->delete() < 0){
                throw new ApiException(BaseError::SAVE_ERROR);
            }

            if(CustomerAudit::deleteAll(['customer_id'=>$paySetting->customer_id,'boss_audit_status'=>CustomerAudit::CUSTOMER_AUDIT_OPEN_PAY]) < 0){
                throw new ApiException(BaseError::SAVE_ERROR);
            }

            if((new CustomerAudit())->find()->where(['customer_id'=>$paySetting->customer_id,'boss_audit_status'=>CustomerAudit::CUSTOMER_AUDIT_SUBMIT_APPLY])->one()->delete() < 0){
                throw new ApiException(BaseError::SAVE_ERROR);
            }

            //操作日志
            $audit['customer_id'] = $paySetting->customer_id;
            $audit['type'] = CustomerAudit::BOSS_AUDIT_STATUS;
            $audit['audit_status'] = CustomerAudit::CUSTOMER_AUDIT_CHECK;
            $audit['status'] = CustomerAudit::AUDIT_STATUS_FAL;
            $audit['title'] = '银行审核结果：不通过';
            $audit['desc'] = $searchParams['audit_remark'];
            (new CustomerAudit())->auditCreate($audit);

            $audit['customer_id'] = $paySetting->customer_id;
            $audit['type'] = CustomerAudit::AGENT_AUDIT_STATUS;
            $audit['audit_status'] = CustomerAudit::AGENT_CUSTOMER_REVIEW_OPEN;
            $audit['status'] = CustomerAudit::AUDIT_STATUS_FAL;
            $audit['title'] = '银行审核结果：不通过';
            $audit['desc'] = $searchParams['audit_remark'];
            (new CustomerAudit())->auditCreate($audit);
        }

        $transaction->commit();
        return true;
    }


    /**
     * 回调数据保存
     */
    public function createCallBack($postParams,$params){

        $callback = array();
//        $params = json_decode($postParams['data']);
        $callback['key'] = md5($params->call_back_func.$params->call_back_type.$params->mch_id.$postParams['_timestamp']);
        $keyUnique = CustomerSpeedpostCallback::findOne(['key'=>$callback['key']]);
        if(!empty($keyUnique)){
            return false;
        }
        $callback['type'] = $params->call_back_type;
        $callback['call_func'] = $params->call_back_func;
        $callback['callback_content'] = $params->function_data;
        $callback['status'] = CustomerSpeedpostCallback::STATUS_DEFAULT;
        $callback['created'] = time();
        $callback['modified'] = time();
        $callbackModel = new CustomerSpeedpostCallback();
        if(!$callbackModel->load(['CustomerSpeedpostCallback'=>$callback]) || !$callbackModel->save()){
            return false;
        }
        return $callbackModel;
    }

    /**
     * 回调数据修改
     */
    public  function updateCallBack($callbackModel,$status){
        $updateCallBack = CustomerSpeedpostCallback::findOne($callbackModel->id);
        $updateCallBack->status = $status;
        if(!$updateCallBack->save()){
            return false;
        }
        return true;
    }
}