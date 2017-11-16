<?php

namespace app\models\agent;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class AgentAccount extends BaseModel
{

    /**账户资产
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%agent_account}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['agent_id','advance_money','pay_money','created','modified','his_pay_money','paying_money'], 'integer'],

        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['deleted' => 1]);

        if (isset($params['name']) && $params['name']) {
            $query->andFilterWhere(['like', 'name', $params['name']]);
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

    /**获取账户信息
     * @param $agent_Id
     * @return array
     */
    public function getAgentInfo($agent_Id)
    {
        $query = self::find()->where(['agent_id' => $agent_Id])->one();
        $model= $this->recordToArray($query);
        $confirmed_money=0;
        if(!empty($model)){
            $query= AgentSettleMonth::find()->where(['agent_id'=>$agent_Id])->andFilterWhere(['<>','proof_status',3]);
            $query->andFilterWhere(['audit_status'=>2]);
            if(!empty($query)){
                foreach ($query->asArray()->all() as $k=>$v){
                    $confirmed_money=$confirmed_money+$v['play_agent_money'];
                }
            }
            $model['confirmed_money'] = $confirmed_money;
        }else{
            $model['confirmed_money']=0;
        }
        return  $model;
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
