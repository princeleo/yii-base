<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/19
 * Time: 20:48
 */

namespace app\common\services\script;
use app\common\helpers\ConstantHelper;
use app\common\ResultModel;
use app\models\shop\ShopBase;
use app\models\shop\ShopSubPaymentSettings;
use app\models\wsh\WshShop;
use app\models\wsh\WshShopSubPaymentSettings;
use yii\base\Exception;
use Yii;

/**
 * Class WshService
 * @package app\common\services\script
 */
class WshService extends BaseService {
    /**
     * @var
     */
    private $nowTime;

    public $totalCount = 0;

    /**
     * 取独立结算列表
     * @param $params
     * @return array
     */
    public function getSubPaymentSettings($params)
    {
        $searchModel = new WshShopSubPaymentSettings();
        $dataProvider = $searchModel->search($params, ['wshShop','wshShop.company','wshShopSubStatementApply']);
        $resultModel = new ResultModel();
        $dataProvider->setPagination(['pageSize' => $params['per-page']]);
        return $resultModel->result($dataProvider->getModels(),$dataProvider->getPagination());
    }

    /**
     * @param $data
     * @return bool
     */
    public function saveSubPaymentSettings($data)
    {
        if (empty($data) || !is_array($data)) {
            return true;
        }
        !empty($this->nowTime) || $this->nowTime = time();

        $fields = 'agent_id,shop_id,shop_sub_id,merchant_id,shop_sub_name,skey,account,sign_key,crypt_key,payment_type,payment_bank,rate,new_rate,effect_time,min_money_per_order,max_money_per_order,max_money_per_day,created,modified,deleted,sync_time';
        $sql = 'REPLACE INTO '.ShopSubPaymentSettings::tableName().' ('.$fields.') values  ';

        $values = '';
        foreach($data as $item) {
            $agent_id = isset($item['wshShop']['company']['sid']) ? $item['wshShop']['company']['sid'] : 0;
            $item['shop_sub_id'] = (empty($item['shop_sub_id']) && !empty($item['merchant_sub_id'])) ? $item['merchant_sub_id'] : $item['shop_sub_id'];
            $skey = md5($item['shop_id'].'-'.$item['merchant_id'].'-'.$item['shop_sub_id'].'-'.$item['payment_type'].'-'.$item['payment_bank'].'-'.ShopSubPaymentSettings::tableName());
            $shopSubName = isset($item['wshShopSubStatementApply']['short_name']) ? $item['wshShopSubStatementApply']['short_name'] : '';
            $values .= '(';
            $values .= $agent_id.',';
            $values .= $item['shop_id'].',';
            $values .= $item['shop_sub_id'].',';
            $values .= $item['merchant_id'].',';
            $values .= '\''.$shopSubName.'\',';
            $values .= '\''.$skey.'\',';
            $values .= '\''.$item['account'].'\',';
            $values .= '\''.$item['sign_key'].'\',';
            $values .= '\''.$item['crypt_key'].'\',';
            $values .= $item['payment_type'].',';
            $values .= $item['payment_bank'].',';
            $values .= $item['rate'].',';
            $values .= $item['new_rate'].',';
            $values .= $item['effect_time'].',';
            $values .= $item['min_money_per_order'].',';
            $values .= $item['max_money_per_order'].',';
            $values .= $item['max_money_per_day'].',';
            $values .= $item['created'].',';
            $values .= $item['modified'].',';
            $values .= $item['deleted'].',';
            $values .= $this->nowTime;
            $values .= '),';
        }
        $values = trim($values, ',');
        if ($values) {
            $sql .= $values.';';
            $transaction = ShopSubPaymentSettings::getDb()->beginTransaction();
            try{
                Yii::$app->db->createCommand($sql)->query();
                Yii::info('ShopSubPaymentSettings SQL exec | success | '.$sql, __METHOD__);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::info('ShopSubPaymentSettings SQL exec | fail | '.$sql, __METHOD__);
                Yii::info('saveSubPaymentSettings SQL exec | exception | '.$e->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * 取商户列表
     * @param $params
     * @return array
     */
    public function getShopList($params)
    {
        $searchModel = new WshShop();
        $dataProvider = $searchModel->search($params, ['company']);

        $resultModel = new ResultModel();
        $dataProvider->setPagination(['pageSize' => $params['per-page'],'page' => $params['page']]);
        return $resultModel->result($dataProvider->getModels(),$dataProvider->getPagination());
    }

    /**
     * @param $data
     * @return bool
     */
    public function saveShopList($data)
    {
        if (empty($data) || !is_array($data)) {
            return true;
        }
        !empty($this->nowTime) || $this->nowTime = time();
        $statusMap = [
            1 => 1, //启用
            2 => -2, //禁用
            3 => -1, //删除
            0 => -3, //异常
        ];

        $appMap = ConstantHelper::$appMap;

        $fields = [
            'shop_id',
            'name',
            'qq',
            'agent_id',
            'pickup_status',
            'brand_label',
            'app_id',
            'contract_no',
            'contract_start',
            'contract_end',
            'tel',
            'website',
            'addr',
            'desc',
            'bg_img',
            'logo',
            'review_status',
            'auto_refund',
            'version',
            'after_sale_time_status',
            'after_sale_handle_time',
            'return_address',
            'return_consignee',
            'return_phone',
            'contact',
            'is_restaurant',
            'merchant_id',
            //'boss_auto_refund',
            'shop_limit',
            'status',
            'created',
            'modified',
            'sync_time'
        ];

        $sql = 'REPLACE INTO '.ShopBase::tableName().' (`'.implode('`,`', $fields).'`) values  ';

        $values = '';
        foreach($data as $itemArr) {
            $shopBase = new ShopBase();
            $item = $itemArr;
            $item['agent_id'] = isset($item['company']['sid']) ? $item['company']['sid'] : 0;
            $item['shop_id'] = $item['id'];
            $item['app_id'] = isset($appMap[$item['self_platform']]) ? $appMap[$item['self_platform']] : $appMap[1];
            $item['status'] = isset($statusMap[$item['deleted']]) ? $statusMap[$item['deleted']] : -4;
            $item['sync_time'] = $this->nowTime;
            $shopBase->loadDefaultValues();
            if (!$shopBase->load(['ShopBase' => $item]) || !$shopBase->validate()) {
                Yii::info('ShopBase validate | failed | '.json_encode($item), __METHOD__);
                continue;
            }
            $item = $shopBase->toArray();
            $values .= '(';
            foreach ($fields as $val) {
                $values .= '\''.$item[$val].'\',';
            }
            $this->totalCount++;
            $values = trim($values, ',');
            $values .= '),';
            unset($item);
            unset($shopBase);
        }
        $values = trim($values, ',');
        if ($values) {
            $sql .= $values.';';
            $transaction = ShopBase::getDb()->beginTransaction();
            try{
                Yii::$app->db->createCommand($sql)->query();
                Yii::info('saveShopList SQL exec | success | '.$sql, __METHOD__);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::info('saveShopList SQL exec | fail | '.$sql, __METHOD__);
                Yii::info('saveShopList SQL exec | exception | '.$e->getMessage(), __METHOD__);
            }
        }
    }
}