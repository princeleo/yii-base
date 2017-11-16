<?php

namespace app\models\agent;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class AgentSettleAudit extends BaseModel
{

    /**结算审核
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%agent_settle_audit}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['reason','action_name','audit_image','key_date'], 'string'],
            [['agent_id', 'shop_id','app_id','month','is_problems','action_id','created','modified','audit_status','proof_status','problem_status','month_id'], 'integer'],

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

        if (isset($params['user_name']) && $params['user_name']) {
            $query->andFilterWhere(['like', 'user_name', $params['user_name']]);
        }
        if (isset($params['status']) && $params['status']) {
            $query->andFilterWhere(['status' => $params['status']]);
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
            '1'=>'待确认',
            '2'=>'财务审核不通过',
            '3'=>'财务审核通过',
            '4'=>'异议审核中',
            '5'=>'异议审核不通过',
            '6'=>'异议审核通过',
            '7'=>'凭证审核中',
            '8'=>'凭证审核不通过',
            '9'=>'已结算',
        ];
    }


    /**审批流程凭证信息查询
     * @param $params
     * @param int $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function GetProofLists($id=0)
    {
        $arr=array();
        $query = self::find()->where(['month_id' => $id]);
        $query->andFilterWhere(['>', 'proof_status', 0]);

        if(!empty($query->asArray()->all()))
        {
            $arr= $query->asArray()->all();
            foreach ($arr as $key=>$value)
            {
                $arr[$key]['name']= AgentSettleMonth::getProofStatus()[$value['proof_status']];
            }


        }
        return $arr;
    }


    /**审批流程异议信息查询
     * @param $params
     * @param int $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function GetProblemLists($id=0)
    {
        $arr=array();
        $query = self::find()->where(['month_id' => $id]);
        $query->andFilterWhere(['>', 'problem_status', 0]);

        if(!empty($query->asArray()->all()))
        {
            $arr= $query->asArray()->all();
            foreach ($arr as $key=>$value)
            {
                $arr[$key]['name']= AgentSettleMonth::getProblemStatus()[$value['problem_status']];
            }
        }
        return $arr;
    }



    /**审批流程异议信息查询
     * @param $params
     * @param int $id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function GetAuditLists($id=0)
    {
        $arr=array();
        $query = self::find()->where(['month_id' => $id]);
            $query->andFilterWhere(['>', 'audit_status', 0]);

        if(!empty($query->asArray()->all()))
        {
            $arr= $query->asArray()->all();
            foreach ($arr as $key=>$value)
            {
                $arr[$key]['name']= AgentSettleMonth::getAuditStatus()[$value['audit_status']];
            }
        }
        return $arr;
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
