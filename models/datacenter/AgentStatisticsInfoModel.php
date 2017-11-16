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

class AgentStatisticsInfoModel extends DataCenterModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'agent_statistics_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'app_id'], 'required'],
            [['app_name',], 'required'],
            [['open_shop_number', 'customer_shop_info', 'pending_audit_shop_number', 'fall_shop_number', 'ready_open_shop_number'], 'required'],
        ];
    }

    /**
     * 搜索列表 API 调用
     * @param $params
     * @return array
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

        if (!empty($params['StartTime']) && !empty($params['EndTime'])) {
            $query->andFilterWhere([">=", "date", $params['StartTime']]);
            $query->andFilterWhere(["<=", "date", $params['EndTime']]);
        }

        if (!empty($params['app'])) {
            $query->andFilterWhere(["app_id" => $params["app"]]);
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
     * 搜索列表
     * @param $params
     * @return array
     */
    public function search($params)
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

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['re_array'])) {
            $query->asArray();
        }

        if (!empty($params['is_all'])) {
            $arr = $query->all();
        } else {
            // limit
            if (!empty($params['limit'])) {
                $data = $this->pager($query, ['pageSize' => $params['limit']]);
            } else {
                $data = $this->pager($query, ['pageSize' => 10]);
            }
            $arr['result'] = $data['result'];
            $arr['pages'] = $data['pages'];
        }

        return $arr;
    }


    /**
     * 获取订单总数据
     * @param $params
     * @return object ActiveDataProvider
     */
    public function Total($params)
    {
        $query = self::find();

        $query->select([
            'agent_id',
            "SUM(open_shop_number) open_shop_number",
            "MAX(count_shop_number) count_shop_number",
            "SUM(pending_audit_shop_number) pending_audit_shop_number",
            "SUM(fail_shop_number) fail_shop_number",
            "SUM(ready_open_shop_number) ready_open_shop_number",
        ]);

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

        $dataProvider = $query->asArray()->one();

        return $dataProvider;
    }
}
