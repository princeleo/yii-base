<?php

namespace app\models\activity;

use app\models\agent\AgentBase;
use app\models\shop\CustomerPaymentSetting;
use app\models\shop\ShopBase;
use Yii;
use yii\data\ActiveDataProvider;


class ActivitySettle extends \app\models\BaseModel
{


    const SHOP_GROUP = 1;    //按商户
    const ACTIVITY_GROUP = 2;  //按活动


    /**
     * 活动结算状态
     */
    const ACTIVITY_SETTLE_NOT = 0;  //未结算
    const ACTIVITY_SETTLE_PART = 1;    //部分结算
    const ACTIVITY_SETTLE_ALREADY = 2;  //已结算
    const ACTIVITY_SETTLE_WITHOUT = 3;    //无需结算
    public static $activitySettleStatus = [
        self::ACTIVITY_SETTLE_NOT => '未结算',
        self::ACTIVITY_SETTLE_PART => '部分结算',
        self::ACTIVITY_SETTLE_ALREADY => '已结算',
        self::ACTIVITY_SETTLE_WITHOUT => '无需结算',
    ];

    public static $shopSettleStatus = [
        self::ACTIVITY_SETTLE_NOT => '未结算',
        self::ACTIVITY_SETTLE_PART => '结算中',
        self::ACTIVITY_SETTLE_ALREADY => '已结算',
        self::ACTIVITY_SETTLE_WITHOUT => '不予结算',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_settle';
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(ActivityModel::className(), ['id' => 'activity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseAgent()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['shop_id' => 'shop_id'])->with('customer');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerSetting()
    {
        return $this->hasOne(CustomerPaymentSetting::className(), ['shop_id' => 'shop_id']);
    }

    /**
     * 活动搜索
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {

        $query = ActivitySettle::find()->where(['group_by'=>2])
            ->leftJoin(ActivityModel::tableName(), ActivitySettle::tableName().".activity_id = ".ActivityModel::tableName().".id");
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!empty($params['activity_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.activity_id' => $params['activity_id']]);
        }
        if (!empty($params['app_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.app_id' => $params['app_id']]);
        }

        if (!empty($params['activity_status'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.activity_status' => ($params['activity_status'] - 1)]);
        }

        if (!empty($params['activity_name'])) {
            $query->andFilterWhere(['like',ActivitySettle::tableName().'.activity_name' , $params['activity_name']]);
        }
        $query->orderBy(ActivityModel::tableName().'.modified desc');
        $query->groupBy(ActivitySettle::tableName().'.activity_id');//pr($query->createCommand()->getSql());die;

        return $dataProvider;
    }


    public function shopSettleSearch($params, $with=[],$all = false)
    {
        $query = ActivitySettle::find()->where(['activity_id'=>$params['activity_id']])->andFilterWhere(['group_by'=>1]);

        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!empty($params['shop_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.shop_id' => $params['shop_id']]);
        }
        if (!empty($params['agent_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.agent_id' => $params['agent_id']]);
        }

        if (!empty($params['shop_name'])) {
            $query->andFilterWhere(['like',ActivitySettle::tableName().'.shop_name' , $params['shop_name']]);
        }
        if (!empty($params['agent_name'])) {
            $query->andFilterWhere(['like',AgentBase::tableName().'.agent_name', $params['agent_name']]);
            $query->leftJoin(AgentBase::tableName(), ActivitySettle::tableName().".agent_id = ".AgentBase::tableName().".agent_id");
        }

        if($all){
            return $query->asArray()->all();
        }
        return $dataProvider;
    }


    public function exportSelectShopId($params, $with=[])
    {
        $query = ActivitySettle::find()->where(['activity_id'=>$params['activity_id']]);
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!empty($params['shop_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.shop_id' => $params['shop_id']]);
        }
        if (!empty($params['agent_id'])) {
            $query->andFilterWhere([ActivitySettle::tableName().'.agent_id' => $params['agent_id']]);
        }

        if (!empty($params['shop_name'])) {
            $query->andFilterWhere(['like',ActivitySettle::tableName().'.shop_name' , $params['shop_name']]);
        }
        if (!empty($params['agent_name'])) {
            $query->andFilterWhere(['like',AgentBase::tableName().'.agent_name', $params['agent_name']]);
            $query->leftJoin(AgentBase::tableName(), ActivitySettle::tableName().".agent_id = ".AgentBase::tableName().".agent_id");
        }

        return $dataProvider;
    }


    /**
     * 统计某个活动的基本信息
     * @param $params
     * @return ActiveDataProvider
     */
    public static function countActivity($params)
    {
        $query = ActivitySettle::find()->where(['activity_id'=>$params['activity_id'],'group_by'=>2]);
        $query->with('activity');
        return $query->asArray()->one();
    }


    /**
     * 获取各个资金状态的统计数
     */
    public function countStatus(){
        $query = ActivitySettle::find()
            ->select([
                'activity_status',
                'count(id) as num',    // 订单数量
            ])
            ->where(['group_by' => 2])
            ->groupBy(ActivitySettle::tableName().'.activity_status')->asArray()->all();
        $array = array();
        if(!empty($query)){
            foreach($query as $val){
                $array[$val['activity_status']] = $val['num'];
            }
        }
        return $array;

    }
}
