<?php

namespace app\common\services;

use app\common\cache\RedisCache;
use app\models\agent\AgentSettleRules;
use app\models\base\SettleGroup;
use yii;
use yii\base\Component;


/**
 * author leo.zou
 * 服务商分佣SERVICE
 * Class AgentSettleService
 * @package app\common\services
 */
class AgentSettleService extends Component
{
    /**
     * @param $agent_id 服务商ID
     * @param $shop_sub_id   分店ID
     * @param $order_id   订单号
     * @param $rate 费率
     * @param $amount  支付金额
     * @param $pay_time 支付时间
     * @param $type 分佣类型
     * @param $app_id 平台
     * @return array(服务商成本费率，分佣金额，规则ID)
     */
    public function getCommission($agent_id,$shop_sub_id,$order_id,$rate,$amount,$pay_time,$type,$app_id)
    {
        $params = func_get_args();
        Yii::info('step 1 | commission request | params :'.json_encode($params), __METHOD__);
        if(empty($agent_id) || (empty($rate) && $type == SettleGroup::SETTLE_TYPE_TRANS)){
            return array(0,0,0);
        }

        $key = 'AgentRules_'.$agent_id;
        $rules = RedisCache::get($key);
        if(empty($rules)){
            $rules = (new AgentSettleRules())->findAgentRules($agent_id);
            RedisCache::set($key,$rules);
        }
        $valid_rule = null;
        if(is_array($rules)){
            foreach($rules as $rule){
                if($pay_time > $rule['start_time'] && $pay_time < $rule['end_time'] && $type == $rule['type'] && $app_id == $rule['app_id']){
                    $valid_rule = $rule;
                    break;
                }
            }
        }
        if(empty($valid_rule)){
            return array(0,0,0);
        }
        Yii::info('step 2 | computation rule | rule :'.json_encode($valid_rule), __METHOD__);


        //检查分佣规则有效性
        if(empty($valid_rule['fields']['field_val']) || !is_numeric($valid_rule['fields']['field_val'])){
            Yii::info('step 2-1 | computation rule  | rule :'.json_encode($valid_rule), __METHOD__);
            return array(0,0,0);
        }

        //计算分佣
        switch($type){
            case SettleGroup::SETTLE_TYPE_TRANS: //流水分佣

                $commission = number_format($amount*($rate-$valid_rule['fields']['field_val'])/100,6,'.','');
                break;

            case SettleGroup::SETTLE_TYPE_CONSUME: //消费分佣
            case SettleGroup::SETTLE_TYPE_OPEN: //商户开户分佣
            case SettleGroup::SETTLE_TYPE_SERVICE: //商户服务费分佣
                $commission = number_format($amount*$valid_rule['fields']['field_val']/100,6,'.','');
                break;

            default:
                return array(0,0,0);
        }
        $commission = $commission < 0 ? 0 : $commission;
        Yii::info('step 3 | commission result  | result :'.$commission, __METHOD__);

        return array(empty($valid_rule['fields']['field_val']) ? 0 : $valid_rule['fields']['field_val'] ,$commission,empty($valid_rule['id']) ? 0 : $valid_rule['id']);
    }
}