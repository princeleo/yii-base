<?php
/**
 * DataCenter AgentOrder库
 * Author:ShiYaoJia
 * Class DataCenterModel extends DataCenterModel
 * @package app\models\datacenter
 */

namespace app\models\datacenter;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\datacenter\AgentStatisticsInfoModel;

class AgentOrderModel extends DataCenterModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'agent_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'shop_id', 'shop_sub_id', 'app_id', 'rule_id', 'group_id'], 'required'],
            [['shop_name', 'shop_sub_name', 'app_name', 'rule_name', 'group_name'], 'required'],
            [['order_number', 'paid_amount', 'order_amount', 'refund_amount', 'commission_number', 'type', 'account'], 'required'],
        ];
    }

    /**
     * 获取订单统计数据列表
     * @param $params
     * @return object ActiveDataProvider
     */
    public function ListAllToApi($params)
    {
        $publicDataName = "a"; // 公共数据别名
        $orderDataName = "b";   // 服务商订单数据别名
        $agentDataName = "c";   // 服务商基础数据别名
        $shopDataName = "d";   // 累积开户数量别名

        $query = self::find();

        $AllSelect = $this->retSelect($params, $publicDataName, $orderDataName, $agentDataName, $shopDataName);

        if (!empty($params['no_promotion'])) {
            foreach ($AllSelect as $k => $v) {
                if ($v == $publicDataName.'.promotion_id' || $v == $publicDataName.'.promotion_name') {
                    unset($AllSelect[$k]);
                }
            }
        }

        $query->select($AllSelect);

        // $a  产生数据的基础条件数据  union 的两个表
        if (empty($params['on_date'])) {    // 是否按时间排序
            $select = ["promotion_id", "promotion_name", "agent_id", "app_id", "app_name", "date"];
        } else {
            $select = ["promotion_id", "promotion_name", "agent_id", "app_id", "app_name"];
        }

        if (!empty($params['no_promotion'])) {
            foreach ($select as $k => $v) {
                if ($v == 'promotion_id' || $v == 'promotion_name') {
                    unset($select[$k]);
                }
            }
        }

        $a1 = self::find()->select($select);
        $a2 = AgentStatisticsInfoModel::find()->select($select);

        // $AgentData  商户订单数据
        if (!empty($params['no_promotion'])) {  // 去除地推员数据 对分组信息有影响
            $select = [
                "agent_id", "app_id", "app_name", "shop_id", "shop_name",
                "SUM(order_number) order_number", "SUM(order_amount) order_amount", "SUM(paid_amount) paid_amount",
                "SUM(refund_amount) refund_amount", "SUM(commission_number) commission_number",
                "business_type", "rule_id", "rule_name", "group_id", "group_name", "type", "pay_account", "date"
            ];
        } else {
            $select = [
                "promotion_id", "promotion_name", "agent_id", "app_id", "app_name", "shop_id", "shop_name",
                "SUM(order_number) order_number", "SUM(order_amount) order_amount", "SUM(paid_amount) paid_amount",
                "SUM(refund_amount) refund_amount", "SUM(commission_number) commission_number",
                "business_type", "rule_id", "rule_name", "group_id", "group_name", "type", "pay_account", "date"
            ];
        }
        $AgentData = self::find()->select($select);

        // $OrderData  商户开户数据
        if (!empty($params['no_promotion'])) {  // 去除地推员数据 对分组信息有影响
            $select = [
                "agent_id", "app_id", "app_name",
                "SUM(open_shop_number) open_shop_number", "MAX(count_shop_number) count_shop_number",
                "SUM(pending_audit_shop_number) pending_audit_shop_number", "SUM(fail_shop_number) fail_shop_number",
                "SUM(ready_open_shop_number) ready_open_shop_number", "date"
            ];
        } else {
            $select = [
                "promotion_id", "promotion_name", "agent_id", "app_id", "app_name",
                "SUM(open_shop_number) open_shop_number", "MAX(count_shop_number) count_shop_number",
                "SUM(pending_audit_shop_number) pending_audit_shop_number", "SUM(fail_shop_number) fail_shop_number",
                "SUM(ready_open_shop_number) ready_open_shop_number", "date"
            ];
        }
        $OrderData = AgentStatisticsInfoModel::find()->select($select);

        // $d  商户每个平台累积开户总数数
        $d = AgentStatisticsInfoModel::find()->select([
            "promotion_id", "promotion_name", "agent_id", "app_id", "app_name", "SUM(count_shop_number) count_shop_number", "date"
        ]);

        // $count_shop_number  地推员累积开户总数数
        $count_shop_number = AgentStatisticsInfoModel::find()->select([
            "promotion_id", "promotion_name", "agent_id", "app_id", "app_name", "MAX(count_shop_number) count_shop_number", "date"
        ]);

        // 添加 where 条件
        $this->AddWhere($params, $a1, $a2, $AgentData, $OrderData, $count_shop_number);

        // 增加分组
        $this->AddGroup($params, $a1, $a2, $AgentData, $OrderData, $count_shop_number, $d);

        $count_shop_number = $count_shop_number->createCommand()->getRawSql();

        $d->from = ['('.$count_shop_number.') '.AgentStatisticsInfoModel::tableName()];    // 累积开户数量

        $a1 = $a1->createCommand()->getRawSql();    // 订单表中包含的数据
        $a2 = $a2->createCommand()->getRawSql();    // 基础表中包含的数据
        $AgentData = $AgentData->createCommand()->getRawSql();  // 基础表的所有数据
        $OrderData = $OrderData->createCommand()->getRawSql();  // 订单表的所有数据
        $d = $d->createCommand()->getRawSql();  // 累积开户数量

        if (isset($params['re_shop'])) {
            $query->from = ["($a2) $publicDataName"];
        } else if (isset($params['re_order'])) {
            $query->from = ["($a1) $publicDataName"];
        } else {
            $query->from = ['('.$a1.' union '.$a2.') '.$publicDataName];
        }

        $this->AddJoin($params, $query, $AgentData, $OrderData, $agentDataName, $publicDataName, $shopDataName, $orderDataName, $d);

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        return $query;
    }

    /**
     * 获取总 select 条件
     * @param $params
     * @param $publicDataName
     * @param $orderDataName
     * @param $agentDataName
     * @param $shopDataName
     * @return array
     */
    private function retSelect($params, $publicDataName, $orderDataName, $agentDataName, $shopDataName)
    {
        if (isset($params['re_shop'])) {    // 只获取商户基本数据 （例如开户数据）
            $select = [
                $publicDataName.'.agent_id',
                $publicDataName.'.promotion_id',
                $publicDataName.'.promotion_name',
                $publicDataName.'.app_id',
                $publicDataName.'.app_name',
                $publicDataName.'.date',
                // 基础信息
                'ifnull('.$agentDataName.'.open_shop_number, 0) open_shop_number',
                'ifnull('.$shopDataName.'.count_shop_number, 0) count_shop_number',
                'ifnull('.$agentDataName.'.pending_audit_shop_number, 0) pending_audit_shop_number',
                'ifnull('.$agentDataName.'.fail_shop_number, 0) fail_shop_number',
                'ifnull('.$agentDataName.'.ready_open_shop_number, 0) ready_open_shop_number',
            ];
        } else if (isset($params['re_order'])) {    // 只获取订单数据
            $select = [
                $publicDataName.'.agent_id',
                $publicDataName.'.promotion_id',
                $publicDataName.'.promotion_name',
                $publicDataName.'.app_id',
                $publicDataName.'.app_name',
                $orderDataName.'.date',
                // 订单详细信息
                'ifnull('.$orderDataName.'.shop_id, 0) shop_id',
                'ifnull('.$orderDataName.'.shop_name, "null") shop_name',
                'ifnull('.$orderDataName.'.business_type, 0) business_type',
                'ifnull('.$orderDataName.'.rule_id, 0) rule_id',
                'ifnull('.$orderDataName.'.rule_name, "null") rule_name',
                'ifnull('.$orderDataName.'.group_id, 0) group_id',
                'ifnull('.$orderDataName.'.group_name, "null") group_name',
                'ifnull('.$orderDataName.'.type, 0) type',
                'ifnull('.$orderDataName.'.pay_account, "null") pay_account',
                // 四项金额
                'ifnull('.$orderDataName.'.order_number, 0) order_number',
                'ifnull('.$orderDataName.'.paid_amount, 0) paid_amount',
                'ifnull('.$orderDataName.'.order_amount, 0) order_amount',
                'ifnull('.$orderDataName.'.refund_amount, 0) refund_amount',
                'ifnull('.$orderDataName.'.commission_number, 0) commission_number',
            ];
        } else if (!empty($params['on_date'])) {    // 不要求比对时间 date  如果比对时间  相同地推员会出现两条记录  不利于后面数据合并  切记
            $select = [
                $publicDataName.'.agent_id',
                $publicDataName.'.promotion_id',
                $publicDataName.'.promotion_name',
                $publicDataName.'.app_id',
                $publicDataName.'.app_name',
                $orderDataName.'.date',
                // 订单详细信息
                'ifnull('.$orderDataName.'.shop_id, 0) shop_id',
                'ifnull('.$orderDataName.'.shop_name, "null") shop_name',
                'ifnull('.$orderDataName.'.business_type, 0) business_type',
                'ifnull('.$orderDataName.'.rule_id, 0) rule_id',
                'ifnull('.$orderDataName.'.rule_name, "null") rule_name',
                'ifnull('.$orderDataName.'.group_id, 0) group_id',
                'ifnull('.$orderDataName.'.group_name, "null") group_name',
                'ifnull('.$orderDataName.'.type, 0) type',
                'ifnull('.$orderDataName.'.pay_account, "null") pay_account',
                // 订单数量、支付金额、等等等
                'ifnull('.$orderDataName.'.order_number, 0) order_number',
                'ifnull('.$orderDataName.'.paid_amount, 0) paid_amount',
                'ifnull('.$orderDataName.'.order_amount, 0) order_amount',
                'ifnull('.$orderDataName.'.refund_amount, 0) refund_amount',
                'ifnull('.$orderDataName.'.commission_number, 0) commission_number',
                // 基础信息
                'ifnull('.$agentDataName.'.open_shop_number, 0) open_shop_number',
                'ifnull('.$shopDataName.'.count_shop_number, 0) count_shop_number',
                'ifnull('.$agentDataName.'.pending_audit_shop_number, 0) pending_audit_shop_number',
                'ifnull('.$agentDataName.'.fail_shop_number, 0) fail_shop_number',
                'ifnull('.$agentDataName.'.ready_open_shop_number, 0) ready_open_shop_number',
            ];
        } else {    // 正常情况下  每个地推员每个时间段每个平台会有一条数据
            $select = [
                $publicDataName.'.agent_id',
                $publicDataName.'.promotion_id',
                $publicDataName.'.promotion_name',
                $publicDataName.'.app_id',
                $publicDataName.'.app_name',
                $publicDataName.'.date',
                // 订单详细信息
                'ifnull('.$orderDataName.'.shop_id, 0) shop_id',
                'ifnull('.$orderDataName.'.shop_name, "null") shop_name',
                'ifnull('.$orderDataName.'.business_type, 0) business_type',
                'ifnull('.$orderDataName.'.rule_id, 0) rule_id',
                'ifnull('.$orderDataName.'.rule_name, "null") rule_name',
                'ifnull('.$orderDataName.'.group_id, 0) group_id',
                'ifnull('.$orderDataName.'.group_name, "null") group_name',
                'ifnull('.$orderDataName.'.type, 0) type',
                'ifnull('.$orderDataName.'.pay_account, "null") pay_account',
                // 四项金额
                'ifnull('.$orderDataName.'.order_number, 0) order_number',
                'ifnull('.$orderDataName.'.paid_amount, 0) paid_amount',
                'ifnull('.$orderDataName.'.order_amount, 0) order_amount',
                'ifnull('.$orderDataName.'.refund_amount, 0) refund_amount',
                'ifnull('.$orderDataName.'.commission_number, 0) commission_number',
                // 基础信息
                'ifnull('.$agentDataName.'.open_shop_number, 0) open_shop_number',
                'ifnull('.$shopDataName.'.count_shop_number, 0) count_shop_number',
                'ifnull('.$agentDataName.'.pending_audit_shop_number, 0) pending_audit_shop_number',
                'ifnull('.$agentDataName.'.fail_shop_number, 0) fail_shop_number',
                'ifnull('.$agentDataName.'.ready_open_shop_number, 0) ready_open_shop_number',
            ];
        }

        return $select;
    }

    /**
     * 获取订单统计数据列表
     * @param $params
     * @param $a1
     * @param $a2
     * @param $AgentData
     * @param $OrderData
     * @param $count_shop_number
     * @return object ActiveDataProvider
     */
    private function AddWhere($params, &$a1, &$a2, &$AgentData, &$OrderData, &$count_shop_number)
    {
        // 按服务商id搜索
        if (!empty($params['agent'])) {
            if (!is_numeric($params['agent']) && !is_array($params['agent'])) {
                $params['agent'] = json_decode($params['agent']);
            }
            $a1->andFilterWhere(["agent_id" => $params['agent']]);
            $a2->andFilterWhere(["agent_id" => $params['agent']]);
            $AgentData->andFilterWhere(["agent_id" => $params['agent']]);
            $OrderData->andFilterWhere(["agent_id" => $params['agent']]);
            $count_shop_number->andFilterWhere(["agent_id" => $params['agent']]);
        }

        // 按地推员id进行搜索
        if (!empty($params['promotion_id']) || (isset($params['promotion_id']) && $params['promotion_id'] == 0)) {
            if (!is_array($params['promotion_id']) && $params['promotion_id'] != 0) {
                $params['promotion_id'] = json_decode($params['promotion_id']);
            }
            $a1->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $a2->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $AgentData->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $OrderData->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $count_shop_number->andFilterWhere(["promotion_id" => $params['promotion_id']]);
        }

        // 按时间
        if (!empty($params['StartTime']) && !empty($params['EndTime'])) {
            $a1->andFilterWhere([">=", "date", $params['StartTime']]);
            $a1->andFilterWhere(["<=", "date", $params['EndTime']]);
            $a2->andFilterWhere([">=", "date", $params['StartTime']]);
            $a2->andFilterWhere(["<=", "date", $params['EndTime']]);
            $AgentData->andFilterWhere([">=", "date", $params['StartTime']]);
            $AgentData->andFilterWhere(["<=", "date", $params['EndTime']]);
            $OrderData->andFilterWhere([">=", "date", $params['StartTime']]);
            $OrderData->andFilterWhere(["<=", "date", $params['EndTime']]);

            $count_shop_number->andFilterWhere(["<=", "date", $params['EndTime']]); // 累积开户数量  不需要开始时间
        }

        // 按平台
        if (!empty($params['app'])) {
//            if (!is_numeric($params['app']) && !is_array($params['app'])) {
//                $params['app'] = json_decode($params['app']);
//            }
            $a1->andFilterWhere(["app_id" => $params["app"]]);
            $a2->andFilterWhere(["app_id" => $params["app"]]);
            $AgentData->andFilterWhere(["app_id" => $params["app"]]);
            $OrderData->andFilterWhere(["app_id" => $params["app"]]);
            $count_shop_number->andFilterWhere(["app_id" => $params["app"]]);
        }

        if (!empty($params['domain_id'])) {
            if (!is_numeric($params['domain_id']) && !is_array($params['domain_id'])) {
                $params['domain_id'] = json_decode($params['domain_id']);
            }
            $a1->andFilterWhere(["region_id" => $params["domain_id"]]);
            $a2->andFilterWhere(["region_id" => $params["domain_id"]]);
            $AgentData->andFilterWhere(["region_id" => $params["domain_id"]]);
            $OrderData->andFilterWhere(["region_id" => $params["domain_id"]]);
            $count_shop_number->andFilterWhere(["region_id" => $params["domain_id"]]);
        }

        // 按区域
        if (!empty($params['region'])) {
            if (!is_numeric($params['region']) && !is_array($params['region'])) {
                $params['region'] = json_decode($params['region']);
            }
            $a1->andFilterWhere(["region_id" => $params["region"]]);
            $AgentData->andFilterWhere(["region_id" => $params["region"]]);
        }

        // 按商户
        if (!empty($params['shop'])) {
            if (!is_numeric($params['shop']) && !is_array($params['shop'])) {
                $params['shop'] = json_decode($params['shop']);
            }
            $a1->andFilterWhere(["shop_id" => $params["shop"]]);
            $AgentData->andFilterWhere(["shop_id" => $params["shop"]]);
        }

        // 按分佣类型
        if (!empty($params['rule'])) {
            $a1->andFilterWhere(["type" => $params["rule"]]);
            $AgentData->andFilterWhere(["type" => $params["rule"]]);
        }

        // 按业务类型
        if (!empty($params['business_type'])) {
            $a1->andFilterWhere(["business_type" => $params["business_type"]]);
            $AgentData->andFilterWhere(["business_type" => $params["business_type"]]);
        }

        // 按商户名称
        if (!empty($params['shop_name'])) {
            $a1->andFilterWhere(["like", self::tableName().".shop_name", $params["shop_name"]]);
            $AgentData->andFilterWhere(["like", self::tableName().".shop_name", $params["shop_name"]]);
        }
    }

    /**
     * 获取订单统计数据列表
     * @param $params
     * @param $a1
     * @param $a2
     * @param $AgentData
     * @param $OrderData
     * @param $count_shop_number
     * @param $d
     * @return object ActiveDataProvider
     */
    private function AddGroup($params, &$a1, &$a2, &$AgentData, &$OrderData, &$count_shop_number, &$d)
    {
        // 订单数据基本数据分组
        if (!empty($params['a1Group'])) {
            $a1->groupBy($params['a1Group']);
        }

        // 基础数据基本数据分组
        if (!empty($params['a2Group'])) {
            $a2->groupBy($params['a2Group']);
        }

        // 基础数据所有数据分组
        if (!empty($params['bGroup'])) {
            $AgentData->groupBy($params['bGroup']);
        }

        // 订单数据所有数据分组
        if (!empty($params['cGroup'])) {
            $OrderData->groupBy($params['cGroup']);
        }

        // 累积开户总数分组
        if (!empty($params['dGroup'])) {
            $count_shop_number->groupBy($params['dGroup']);
        }

        // 累积开户总数分组
        if (!empty($params['d_oGroup'])) {
            $d->groupBy($params['d_oGroup']);
        }
    }

    /**
     * 获取订单统计数据列表
     * @param $params
     * @param $query
     * @param $agentDataName
     * @param $AgentData
     * @param $OrderData
     * @param $publicDataName
     * @param $shopDataName
     * @param $orderDataName
     * @param $d
     * @return object ActiveDataProvider
     */
    private function AddJoin($params, &$query, &$AgentData, &$OrderData, &$agentDataName, &$publicDataName, &$shopDataName, &$orderDataName, &$d)
    {
        if (isset($params['re_shop'])) {
            if (empty($params['on_date'])) {    // 不按时间分组
                if (!empty($params['no_promotion'])) {  // 不按地推员分组
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.date = '.$agentDataName.'.date)');
                } else {
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$agentDataName.'.promotion_id and '.$publicDataName.'.date = c.date)');
                }
                $query->leftJoin('('.$d.') '.$shopDataName.' on ('.$publicDataName.'.agent_id = '.$shopDataName.'.agent_id and '.$publicDataName.'.app_id = '.$shopDataName.'.app_id and '.$publicDataName.'.date = '.$shopDataName.'.date)');
            } else {
                $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$agentDataName.'.promotion_id)');
                $query->leftJoin('('.$d.') '.$shopDataName.' on ('.$publicDataName.'.agent_id = '.$shopDataName.'.agent_id and '.$publicDataName.'.app_id = '.$shopDataName.'.app_id)');
            }
        } else if (isset($params['re_order'])) {
            if (empty($params['on_date'])) {    // 不按时间分组
                if (!empty($params['no_promotion'])) {  // 不按地推员分组
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.date = '.$orderDataName.'.date)');
                } else {
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$orderDataName.'.promotion_id and '.$publicDataName.'.date = '.$orderDataName.'.date)');
                }
            } else {
                if (!empty($params['no_promotion'])) {  // 不按地推员分组
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id)');
                } else {
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$orderDataName.'.promotion_id)');
                }
            }
        } else {
            if (empty($params['on_date'])) {    // 不按时间分组
                if (!empty($params['no_promotion'])) {  // 不按地推员分组
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.date = '.$orderDataName.'.date)');
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.date = '.$agentDataName.'.date)');
                } else {
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$orderDataName.'.promotion_id and '.$publicDataName.'.date = '.$orderDataName.'.date)');
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$agentDataName.'.promotion_id and '.$publicDataName.'.date = '.$agentDataName.'.date)');
                }
                $query->leftJoin('('.$d.') '.$shopDataName.' on ('.$publicDataName.'.agent_id = '.$shopDataName.'.agent_id and '.$publicDataName.'.app_id = '.$shopDataName.'.app_id and '.$publicDataName.'.date = '.$shopDataName.'.date)');
            } else {
                if (!empty($params['no_promotion'])) {  // 不按地推员分组
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id)');
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id)');
                } else {
                    $query->leftJoin('('.$AgentData.') '.$orderDataName.' on ('.$publicDataName.'.agent_id = '.$orderDataName.'.agent_id and '.$publicDataName.'.app_id = '.$orderDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$orderDataName.'.promotion_id)');
                    $query->leftJoin('('.$OrderData.') '.$agentDataName.' on ('.$publicDataName.'.agent_id = '.$agentDataName.'.agent_id and '.$publicDataName.'.app_id = '.$agentDataName.'.app_id and '.$publicDataName.'.promotion_id = '.$agentDataName.'.promotion_id)');
                }
                $query->leftJoin('('.$d.') '.$shopDataName.' on ('.$publicDataName.'.agent_id = '.$shopDataName.'.agent_id and '.$publicDataName.'.app_id = '.$shopDataName.'.app_id)');
            }
        }
    }

    /**
     * 获取订单统计数据列表
     * @param $params
     * @return object ActiveDataProvider
     */
    public function ListToApi($params)
    {
        $query = self::find();

        if (!empty($params['select'])) {
            $query->select($params['select']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        if (!empty($params['agent'])) {
            $query->where(["agent_id" => $params['agent']]);
        }

        if (!empty($params['promotion_id'])) {
            if (!is_array($params['promotion_id'])) {
                $params['promotion_id'] = json_decode($params['promotion_id']);
            }
            $query->andFilterWhere(["promotion_id" => $params['promotion_id']]);
        }

        if (!empty($params['StartTime']) && !empty($params['EndTime'])) {
            $query->andFilterWhere([">=", "date", $params['StartTime']]);
            $query->andFilterWhere(["<=", "date", $params['EndTime']]);
        }

        if (!empty($params['app'])) {
            $query->andFilterWhere(["app_id" => $params["app"]]);
        }

        if (!empty($params['shop'])) {
            $query->andFilterWhere(["shop_id" => $params["shop"]]);
        }

        if (!empty($params['shop_name'])) {
            $query->andFilterWhere(["like", "shop_name", $params["shop_name"]]);
        }

        if (!empty($params['shop_sub_id'])) {
            $query->andFilterWhere(["shop_sub_id" => $params["shop_sub_id"]]);
        }

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /**
     * 获取订单总数据
     * @param $params
     * @return object ActiveDataProvider
     */
    public function Total($params)
    {
        $query = self::find();
        $AgentData = AgentStatisticsInfoModel::find();

        $query->select([
            "count(distinct ".self::tableName().".shop_id) shop_num",
            "count(distinct ".self::tableName().".shop_sub_id) shop_sub_num",
            "SUM(".self::tableName().".order_number) order_number",
            "SUM(".self::tableName().".paid_amount) paid_amount",
            "SUM(".self::tableName().".order_amount) order_amount",
            "SUM(".self::tableName().".refund_amount) refund_amount",
            "SUM(".self::tableName().".commission_number) commission_number",
        ]);

        $AgentData->select([
            "SUM(open_shop_number) open_shop_number",
//            "SUM(count_shop_number) count_shop_number",
            "SUM(pending_audit_shop_number) pending_audit_shop_number",
            "SUM(fail_shop_number) fail_shop_number",
            "SUM(ready_open_shop_number) ready_open_shop_number",
        ]);

        $OrderData = AgentStatisticsInfoModel::find()->select([
            "MAX(count_shop_number) count_shop_number",
        ]);

        $d = AgentStatisticsInfoModel::find()->select([
            "SUM(count_shop_number) count_shop_number",
        ]);

        if (!empty($params['agent'])) {
            if (!is_numeric($params['agent']) && !is_array($params['agent'])) {
                $params['agent'] = json_decode($params['agent']);
            }
            $query->andFilterWhere(["agent_id" => $params['agent']]);
            $AgentData->andFilterWhere(["agent_id" => $params['agent']]);
            $OrderData->andFilterWhere(["agent_id" => $params['agent']]);
        }

        if (!empty($params['app'])) {
//            if (!is_numeric($params['app']) && !is_array($params['app'])) {
//                $params['app'] = json_decode($params['app']);
//            }
            $query->andFilterWhere(["app_id" => $params['app']]);
            $AgentData->andFilterWhere(["app_id" => $params['app']]);
            $OrderData->andFilterWhere(["app_id" => $params['app']]);
        }

        if (!empty($params['domain_id'])) {
            if (!is_numeric($params['domain_id']) && !is_array($params['domain_id'])) {
                $params['domain_id'] = json_decode($params['domain_id']);
            }
            $query->andFilterWhere(["region_id" => $params['domain_id']]);
            $AgentData->andFilterWhere(["region_id" => $params['domain_id']]);
            $OrderData->andFilterWhere(["region_id" => $params['domain_id']]);
        }

        if (!empty($params['promotion_id']) || (isset($params['promotion_id']) && $params['promotion_id'] == 0)) {
            if (!is_array($params['promotion_id']) && $params['promotion_id'] != 0) {
                $params['promotion_id'] = json_decode($params['promotion_id']);
            }
            $query->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $AgentData->andFilterWhere(["promotion_id" => $params['promotion_id']]);
            $OrderData->andFilterWhere(["promotion_id" => $params['promotion_id']]);
        }

        if (!empty($params['shop'])) {
            if (!is_numeric($params['shop']) && !is_array($params['shop'])) {
                $params['shop'] = json_decode($params['shop']);
            }
            $query->andFilterWhere(["shop_id" => $params["shop"]]);
        }

        if (!empty($params['shop_name'])) {
            $query->andFilterWhere(["like", self::tableName().".shop_name", $params["shop_name"]]);
        }

        // 按分佣类型
        if (!empty($params['rule'])) {
            $query->andFilterWhere(["type" => $params["rule"]]);
        }

        // 按业务类型
        if (!empty($params['business_type'])) {
            $query->andFilterWhere(["business_type" => $params["business_type"]]);
        }

        if (!empty($params['StartTime']) && !empty($params['EndTime'])) {
            $query->andFilterWhere([">=", "date", $params['StartTime']]);
            $query->andFilterWhere(["<=", "date", $params['EndTime']]);
            $AgentData->andFilterWhere([">=", "date", $params['StartTime']]);
            $AgentData->andFilterWhere(["<=", "date", $params['EndTime']]);
            $OrderData->andFilterWhere(["<=", "date", $params['EndTime']]);
        }

        $OrderData->groupBy("agent_id, app_id, promotion_id");

        $OrderData = $OrderData->createCommand()->getRawSql();

        $d->from = ['('.$OrderData.') '.AgentStatisticsInfoModel::tableName()];

        $d = $d->createCommand()->getRawSql();
        $AgentData = $AgentData->createCommand()->getRawSql();
        $query = $query->createCommand()->getRawSql();

//        echo $d.'-------------------'.$AgentData.'--------------------'.$query; exit;

        $dataProvider = self::findBySql($query)->asArray()->one();
        $AgentData = self::findBySql($AgentData)->asArray()->one();
        $d = self::findBySql($d)->asArray()->one();

        $dataProvider = array_merge($dataProvider, $AgentData, $d);

        return $dataProvider;
    }


    /**
     * 关联基础信息
     */
    public function getStatisticsInfo()
    {
        return $this->hasMany(AgentStatisticsInfoModel::className(),['agent_id'=>'agent_id', 'promotion_id' => 'promotion_id', "app_id" => "app_id"])
            ->select([

            ]);
    }
}
