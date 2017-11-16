<?php

namespace app\models\agent;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class AgentApplyCash extends BaseModel
{
    const APPLY_AUDIT_FAIL_STATUS = -2;//审核失败
    const APPLY_STAY_AUDIT_STATUS = -1;//待审核
    const APPLY_DEFAULT_STATUS = 0;//默认值
    const APPLY_STAY_STATUS = 1;//待转账
    const APPLY_SUCCESS_STATUS = 2;//提现成功
    const APPLY_FAIL_STATUS = 3;//提现失败
    /**用户提现
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%agent_apply_cash}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['agent_id', 'status', 'is_bill', 'pay_money', 'created', 'modified'], 'integer'],
            [['apply_id'], 'string', 'max' => 200],
            [['agent_name', 'action_name'], 'string', 'max' => 50],
            [['audit_description', 'trade_description'], 'string', 'max' => 5000],
            [['bill_number'], 'string', 'max' => 500],
            [['express_number'], 'string', 'max' => 100],
            [['apply_id'], 'unique']
        ];
    }




    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find();
        if (isset($params['agent_name']) && $params['agent_name']) {
            $query->andFilterWhere(['like', 'agent_name', $params['agent_name']]);
        }
        if (isset($params['apply_id']) && $params['apply_id']) {
            $query->andFilterWhere(['like','apply_id',$params['apply_id']]);
        }
        if (isset($params['bill_number']) && $params['bill_number']) {
            $query->andFilterWhere(['like', 'bill_number', $params['bill_number']]);
        }
        if (isset($params['express_number']) && $params['express_number']) {
            $query->andFilterWhere(['like', 'express_number', $params['express_number']]);
        }
        if (isset($params['status']) && $params['status']) {
            $query->andFilterWhere(['status' => $params['status']]);
        }
        if (isset($params['search']) && $params['search']) {
            $query->andFilterWhere([$params['search-type'] => $params['search']]);
        }
        if(!empty($params['date_s'])){
            $query->andWhere(['>','created',$params['date_s']]);
        }
        if(!empty($params['date_e'])){
            $query->andWhere(['<','created',$params['date_e']]);
        }

        if(isset($params['status'])&&$params['status']==self::APPLY_FAIL_STATUS)
        {
            $query->orFilterWhere(['status' => self::APPLY_AUDIT_FAIL_STATUS]);
        }





        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }
    /**状态集合
     * @return array
     */
    public static  function getStatus(){
        return [
            self::APPLY_AUDIT_FAIL_STATUS=>'提现失败',
            self::APPLY_STAY_AUDIT_STATUS=>'待审核',
            self::APPLY_STAY_STATUS=>'待转账',
            self::APPLY_SUCCESS_STATUS=>'提现成功',
            self::APPLY_FAIL_STATUS=>'提现失败'
        ];
    }







    /**根据公告ID回去数据
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
    }


}
