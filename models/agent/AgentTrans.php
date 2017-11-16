<?php

namespace app\models\agent;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class AgentTrans extends BaseModel
{

    /**交易信息
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%agent_apply_audit}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['agent_id', 'account_type', 'trade_money', 'trade_type', 'trade_status', 'pay_time', 'created', 'modified'], 'integer'],
            [['apply_id', 'account_number'], 'string', 'max' => 200],
            [['agent_name', 'pay_method', 'info_source', 'province', 'city', 'key_date', 'account_name', 'account_bank', 'account_branch', 'mobile_phone', 'trade_memo', 'trade_description', 'action_name'], 'string', 'max' => 50],
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

        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
          }

        if (isset($params['trade_type']) && $params['trade_type']) {
            $query->andFilterWhere(['trade_type' => $params['trade_type']]);
        }
        if (isset($params['trade_status']) && $params['trade_status']) {
            $query->andFilterWhere(['trade_status' => $params['trade_status']]);
        }
        if (!empty($params['create_time_e']) && !empty($params['create_time_s'])) {

            $startTime = strtotime($params['create_time_s']);
            $endTime = strtotime($params['create_time_e']);
            $query->andFilterWhere(['between', 'created', $startTime,$endTime]);
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
            '-2'=>'提现失败',
            '-1'=>'待审核',
            '1'=>'待转账',
            '2'=>'提现成功',
            '3'=>'提现失败',
        ];
    }
    /**类型集合
     * @return array
     */
    public static  function getType(){
        return [
            '1'=>'月结转入',
            '2'=>'申请提现',

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
