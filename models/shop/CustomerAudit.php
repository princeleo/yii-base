<?php

namespace app\models\shop;

use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\common\exceptions\ParamsException;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "consume_order".
 *
 * @property string $id
 * @property integer $consume_date
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $exposure
 * @property integer $click
 * @property integer $ctr
 * @property integer $cpm
 * @property integer $cost
 * @property string $src
 * @property integer $created
 * @property integer $modified
 * @property integer $cpc
 */
class CustomerAudit extends \app\models\BaseModel
{

    const BOSS_AUDIT_STATUS = 1;    //boss审核流程
    const AGENT_AUDIT_STATUS = 2;  //服务商审核流程

    const AUDIT_STATUS_SUBMIT = 1;    //提单
    const AUDIT_STATUS_SUC = 1;    //审核通过
    const AUDIT_STATUS_FAL = 2;  //审核不通过
    /**
     * 商户开通-详情步骤
     */
    const CUSTOMER_AUDIT_DRAFTS = 1;  //服务商提单
    const CUSTOMER_AUDIT_CHECK = 2;    //平台审核
    const CUSTOMER_AUDIT_SUBMIT_APPLY = 3;  //提交开户资料
    const CUSTOMER_AUDIT_OPEN_PAY = 4;    //开通支付账户
    const CUSTOMER_AUDIT_ACCOUNT_CHECK = 5;  //录入商户号及校验
    const CUSTOMER_AUDIT_SUCCESS = 6;  //完成开户

    /**
     * 商户开通-详情步骤
     */
    const AGENT_CUSTOMER_APPLY_UPDATE = 1;  //商户资料编辑
    const AGENT_CUSTOMER_REVIEW_OPEN = 2;    //平台审核并开户
    const AGENT_CUSTOMER_OPEN_PAY = 3;  //开通支付账户
    const AGENT_CUSTOMER_AUDIT_SUCCESS = 4;    //完成开户
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_audit';
    }

    public function detail($customer_id,$type){
        $cus_audit['audit'] = CustomerAudit::find()->where(['customer_id'=>$customer_id])->orderBy('created asc')->asArray()->all();
        $boss_status = array();
        $agent_status = array();
        //各个状态的标志
        foreach($cus_audit['audit'] as $key => $value){
            if($value['user_type'] == self::BOSS_AUDIT_STATUS){
                switch($value['boss_audit_status']){
                    case self::CUSTOMER_AUDIT_CHECK:
                        $boss_status['second'] = self::CUSTOMER_AUDIT_CHECK;
                        break;
                    case self::CUSTOMER_AUDIT_SUBMIT_APPLY:
                        $boss_status['third'] = self::CUSTOMER_AUDIT_SUBMIT_APPLY;
                        break;
                    case self::CUSTOMER_AUDIT_OPEN_PAY:
                        $boss_status['fourth'] = self::CUSTOMER_AUDIT_OPEN_PAY;
                        break;
                    case self::CUSTOMER_AUDIT_ACCOUNT_CHECK:
                        $boss_status['fifth'] = self::CUSTOMER_AUDIT_ACCOUNT_CHECK;
                        break;
                    case self::CUSTOMER_AUDIT_SUCCESS:
                        $boss_status['sixth'] = self::CUSTOMER_AUDIT_SUCCESS;
                        break;
                }
            }
            if($value['user_type'] == self::AGENT_AUDIT_STATUS){
                switch($value['agent_audit_status']){
                    case self::AGENT_CUSTOMER_REVIEW_OPEN:
                        $agent_status['second'] = self::AGENT_CUSTOMER_REVIEW_OPEN;
                        break;
                    case self::AGENT_CUSTOMER_OPEN_PAY:
                        $agent_status['third'] = self::AGENT_CUSTOMER_OPEN_PAY;
                        break;
                    case self::AGENT_CUSTOMER_AUDIT_SUCCESS:
                        $agent_status['fourth'] = self::AGENT_CUSTOMER_AUDIT_SUCCESS;
                        break;
                }
            }
        }
        if($type == self::BOSS_AUDIT_STATUS){
            $cus_audit['audit_status'] =$boss_status;
        }else{
            $cus_audit['audit_status'] =$agent_status;
        }
        return $cus_audit;
    }


    public function auditCreate($params){
        $auditModel = new CustomerAudit();
        $auditModel->user_type = $params['type'];
        if($auditModel->user_type == self::BOSS_AUDIT_STATUS){
            $auditModel->boss_audit_status = $params['audit_status'];
        }else{
            $auditModel->agent_audit_status = $params['audit_status'];
        }
        $auditModel->title = isset($params['title'])?$params['title']:"";
        $auditModel->status = isset($params['status'])?$params['status']:0;
        $auditModel->customer_id = $params['customer_id'];
        $auditModel->desc = isset($params['desc'])?$params['desc']:"";
        if(!$auditModel->save()){
            Yii::error($auditModel->errors,'auditCreate');
            throw new ApiException(BaseError::SAVE_ERROR);
        }
    }

}
