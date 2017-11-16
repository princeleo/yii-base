<?php
/**
 * DataCenterSource ElephantOrder 库
 * Author:ShiYaoJia
 * Class ElephantOrderModel extends DataCenterSourceModel
 * @package app\models\datacenter
 */

namespace app\models\datacenter_source;

use app\models\base\BaseDomain;
use app\models\shop\Customer;
use app\models\shop\ShopBase;
use Yii;

class ElephantOrderModel extends DataCenterSourceModel
{
    const ORDER_SCAN_CODE = 1; // 扫码订单

    const PAY_TO_COMPLETE       = 3;    // 支付完成
    const HAVE_ORDER            = 4;    // 已接单
    const HAS_BEEN_COMPLETED    = 5;    // 已完成

    // 统计用 （运营订单状态标准）
    public static $Op_order_status_conf = [
        self::PAY_TO_COMPLETE, self::HAVE_ORDER, self::HAS_BEEN_COMPLETED
    ];

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'elephant_order';
    }

    /**
     * 获取已激活商户列表    (第一版、只需要扫码订单）
     * @param int(11) $EndTime 截至时间（时间戳）
     * @return array
     */
    public function __activatedShopList($EndTime){
        $query = self::find()->select([
            "agent_id", "shop_id"
        ])->where(["<", "pay_time", $EndTime])
        ->andFilterWhere(["order_type" => self::ORDER_SCAN_CODE])
        ->andFilterWhere(["in", "process_status", self::$Op_order_status_conf])
        ->groupBy("agent_id, shop_id")->indexBy("shop_id");
        return array_keys($query->asArray()->all());
    }

    /**
     * 获取区域信息
     * @param int(11) $EndTime 截至时间（时间戳）
     * @return array
     */
    public function __domainList($EndTime){
        // 查出服务商与商户的对应区域
        $query = ShopBase::find()->select("agent_id")->where([
            "<", "created", $EndTime
        ])->andFilterWhere(["app_id" => 100001])->groupBy("agent_id")->indexBy("agent_id");
        $params['agent_id'] = array_keys($query->asArray()->all());
        $domainList = BaseDomain::AgentAndShopGetDomain($params);
        return $domainList;
    }

    /**
     * 获取商户总量
     * @param int(11) $EndTime 截至时间（时间戳）
     * @return array
     */
    public function __shopCount($EndTime){
        $query = ShopBase::find()->select([
            ShopBase::tableName().".agent_id", ShopBase::tableName().".shop_id",
            Customer::tableName().".belong_promotion promotion_id"
        ])
            ->from(ShopBase::tableName())
            ->leftJoin(Customer::tableName(), Customer::tableName().".id = ".ShopBase::tableName().".customer_id")
            ->where(["<", ShopBase::tableName().".created", $EndTime])
            ->andFilterWhere([ShopBase::tableName().".app_id" => 100001])
            ->groupBy(ShopBase::tableName().".shop_id");

        $sql = $query->createCommand()->getRawSql();

        $data = ShopBase::findBySql($sql)->asArray()->all();

        return $data;
    }

    /**
     * 新增商户数量
     * @param int(11) $EndTime 截至时间（时间戳）
     * @param int(11) $StartTime 开始时间（时间戳）
     * @return array
     */
    public function __newShop($EndTime, $StartTime){
        $query = ShopBase::find()->select([
            ShopBase::tableName().".agent_id", ShopBase::tableName().".shop_id",
            Customer::tableName().".belong_promotion promotion_id"
        ])
        ->from(ShopBase::tableName())
        ->leftJoin(Customer::tableName(), Customer::tableName().".id = ".ShopBase::tableName().".customer_id")
        ->where(["<", ShopBase::tableName().".created", $EndTime])
        ->andFilterWhere([">=", ShopBase::tableName().".created", $StartTime])
        ->andFilterWhere([ShopBase::tableName().".app_id" => 100001])
        ->groupBy(ShopBase::tableName().".shop_id");

        $sql = $query->createCommand()->getRawSql();

        $data = ShopBase::findBySql($sql)->asArray()->all();

        return $data;
    }

    /**
     * 获取未激活商户数量
     * @param int (11) $EndTime 截止时间戳
     * @param array $ActivatedShopList 已激活的商户列表
     * @return array $data
     */
    public function __noActivatedShopList($EndTime, $ActivatedShopList){
        // 查出未激活的商户列表
        $query = ShopBase::find()->select([
            ShopBase::tableName().".agent_id", ShopBase::tableName().".shop_id",
            Customer::tableName().".belong_promotion promotion_id"
        ])
        ->from(ShopBase::tableName())
        ->leftJoin(Customer::tableName(), Customer::tableName().".id = ".ShopBase::tableName().".customer_id")
        ->where(["<", ShopBase::tableName().".created", $EndTime])
        ->andFilterWhere([ShopBase::tableName().".app_id" => 100001])
        ->groupBy(ShopBase::tableName().".shop_id");
        if (!empty($ActivatedShopList)) {
            $query->andFilterWhere(["not in", ShopBase::tableName().".shop_id", $ActivatedShopList]);
        }
        $sql = $query->createCommand()->getRawSql();

        $data = ShopBase::findBySql($sql)->asArray()->all();
        return $data;
    }


    /**
     * 商户订单数量列表    (第一版、只需要扫码订单）
     * @param int(11) $StartTime 开始时间（时间戳）
     * @param int(11) $EndTime 截至时间（时间戳）
     * @param array $ActivatedShopList 已激活的商户
     * @return array
     */
    public function __shopOrderNumber($StartTime, $EndTime, $ActivatedShopList){
        $query = self::find()->select([
            "shop_id", "count(shop_id) order_num"
        ])->andFilterWhere(["order_type" => self::ORDER_SCAN_CODE])
        ->andFilterWhere(["in", "process_status", self::$Op_order_status_conf])
        ->andFilterWhere([">=", "pay_time", $StartTime])
        ->andFilterWhere(["<", "pay_time", $EndTime]);
        if (!empty($ActivatedShopList)) {
            $query->andFilterWhere(["in", "shop_id", $ActivatedShopList]);
        }
        $query->groupBy("shop_id");
        $list = $query->asArray()->all();

        return $list;
    }


    /**
     * 正常营业商户列表
     * @param array $ShopOrderNumberList 商户当日订单数量列表
     * @return array $retData
     */
    public function __openAsUsual($ShopOrderNumberList){
        $retData = $ShopIds = [];
        foreach ($ShopOrderNumberList as $k => $v) {
            if ($v['order_num'] > 0 && $v['order_num'] < 5) {
                $ShopIds[] = $v['shop_id'];
            }
        }

        if (!empty($ShopIds)) {
            $retData = $this->__shopidsToInfo($ShopIds);
        }

        return $retData;
    }

    /**
     * 活跃商户列表
     * @param array $ShopOrderNumberList 商户当日订单数量列表
     * @return array $retData
     */
    public function __active($ShopOrderNumberList){
        $retData = $ShopIds = [];
        foreach ($ShopOrderNumberList as $k => $v) {
            if ($v['order_num'] > 4) {
                $ShopIds[] = $v['shop_id'];
            }
        }

        if (!empty($ShopIds)) {
            $retData = $this->__shopidsToInfo($ShopIds);
        }

        return $retData;
    }

    /**
     * 活跃商户列表
     * @param array $ShopOrderNumberList_3 最近三日产生订单的商户列表
     * @param array $ShopOrderNumberList 当日产生订单的商户列表
     * @return array $retData
     */
    public function __noBusiness($ShopOrderNumberList_3, $ShopOrderNumberList){
        $retData = $ShopList_3 = $ShopList = [];
        foreach ($ShopOrderNumberList as $v) {
            $ShopList[] = $v['shop_id'];
        }
        foreach ($ShopOrderNumberList_3 as $v) {
            $ShopList_3[] = $v['shop_id'];
        }

        $ShopIds = array_diff($ShopList_3, $ShopList);

        if (!empty($ShopIds)) {
            $retData = $this->__shopidsToInfo($ShopIds);
        }

        return $retData;
    }

    /**
     * 沉默营业商户列表
     * @param array $ShopOrderNumberList_30 近三十日产生订单的商户列表
     * @param array $ShopOrderNumberList_3 近三日产生订单的商户列表
     * @return array $retData
     */
    public function __silentBusiness($ShopOrderNumberList_30, $ShopOrderNumberList_3){
        $retData = $ShopList_3 = $ShopList_30 = [];
        foreach ($ShopOrderNumberList_30 as $v) {
            $ShopList_30[] = $v['shop_id'];
        }
        foreach ($ShopOrderNumberList_3 as $v) {
            $ShopList_3[] = $v['shop_id'];
        }

        $ShopIds = array_diff($ShopList_30, $ShopList_3);

        if (!empty($ShopIds)) {
            $retData = $this->__shopidsToInfo($ShopIds);
        }
        return $retData;
    }

    /**
     * 流失商户列表
     * @param array $ShopOrderNumberList_30 近三十日产生订单的商户列表
     * @param array $ActivatedShopList 激活的商户列表
     * @return array $retData
     */
    public function __runAway($ShopOrderNumberList_30, $ActivatedShopList){
        $retData = $ShopIds = [];
        foreach ($ShopOrderNumberList_30 as $k => $v) {
            $ShopIds[] = $v['shop_id'];
        }
        $ShopIds = array_diff($ActivatedShopList, $ShopIds);
        if (!empty($ShopIds)) {
            $retData = $this->__shopidsToInfo($ShopIds);
        }

        return $retData;
    }

    // 用 shop_id 组 换取 shop 详情
    private function __shopidsToInfo($ShopIds){
        $query = ShopBase::find()->select([
            ShopBase::tableName().".agent_id", ShopBase::tableName().".shop_id",
            Customer::tableName().".belong_promotion promotion_id"
        ])->from(ShopBase::tableName())
        ->andFilterWhere(["in", ShopBase::tableName().".shop_id", $ShopIds])
        ->leftJoin(Customer::tableName(), Customer::tableName().".id = ".ShopBase::tableName().".customer_id")
        ->andFilterWhere([ShopBase::tableName().".app_id" => 100001])
        ->groupBy(ShopBase::tableName().".shop_id");
        $sql = $query->createCommand()->getRawSql();

        return ShopBase::findBySql($sql)->asArray()->all();
    }
}
