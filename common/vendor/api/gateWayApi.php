<?php

namespace app\common\vendor\api;
use Yii;

class gateWayApi
{

    /**
     * 商户创建
     */
    public function getShopCreate($params)
    {
        $apiParams = [
            'app_id' => isset($params['app_id']) ? $params['app_id'] : null,
            'agent_id' => isset($params['agent_id']) ? $params['agent_id'] : null,
            'customer_id' => isset($params['id']) ? $params['id'] : null,
            'indu_id' => isset($params['indu_id']) ? $params['indu_id'] : null,
            'logo' => isset($params['logo']) ? $params['logo'] : null,
            'province' => isset($params['province_id']) ? $params['province_id'] : null,
            'city' => isset($params['city_id']) ? $params['city_id'] : null,
            'dist' => isset($params['dist_id']) ? $params['dist_id'] : null,
            'addr' => isset($params['address']) ? $params['address'] : null,
            'contact' => isset($params['headman']) ? $params['headman'] : null,
            'mobile' => isset($params['mobile']) ? $params['mobile'] : null,
            'remark' =>null,
            'name' => isset($params['name']) ? $params['name'] : null,
            'short_name' => isset($params['short_name']) ? $params['short_name'] : null,
            'email' => isset($params['email']) ? $params['email'] : null,
            'door_pic' => isset($params['door_pic']) ? $params['door_pic'] : null,
            'bank_cardno' => isset($params['bank_cardno']) ? $params['bank_cardno'] : null,
            'open_account_owner' => isset($params['open_account_owner']) ? $params['open_account_owner'] : null,
            'bank_branch' => isset($params['bank_branch']) ? $params['bank_branch'] : null,
            'rate_scan' => isset($params['rate_scan']) ? $params['rate_scan'] : null,
            'rate_table' => isset($params['rate_table']) ? $params['rate_table'] : null,
        ];
        return $apiParams;
    }

    /**
     * 商户号同步
     */
    public function syncAccount($params)
    {
        $apiParams = [
            'merc_id' => isset($params['shop_id']) ? $params['shop_id'] : null,
            'payment_bank' => isset($params['payment_type']) ? $params['payment_type'] : null,
            'account' => isset($params['account']) ? $params['account'] : null,
            'sign_key' => isset($params['sign_key']) ? $params['sign_key'] : null,
            'crypt_key' => isset($params['sign_key']) ? $params['sign_key'] : null,
            'pay_settlement_rate' => isset($params['rate_table']) ? $params['rate_table'] : null,
            'collect_settlement_rate' => isset($params['rate_scan']) ? $params['rate_scan'] : null,
        ];
        return $apiParams;
    }

    /**
     * 结算信息同步所需数据
     * @param $params
     * @return array
     */
    public function SynchronizationSettlement($params)
    {
        $apiParams = [
            'merc_id' => isset($params['shop_id']) ? $params['shop_id'] : null,
            'province' => isset($params['province_id']) ? $params['province_id'] : null,
            'city' => isset($params['city_id']) ? $params['city_id'] : null,
            'dist' => isset($params['dist_id']) ? $params['dist_id'] : null,
            'addr' => isset($params['address']) ? $params['address'] : null,
        ];
        return $apiParams;
    }

}
