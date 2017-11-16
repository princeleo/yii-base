<?php

namespace app\models\agent;

use app\common\errors\BaseError;
use app\models\BaseModel;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_account_trans".
 *
 * @property integer $id
 * @property string $trans_no
 * @property integer $agent_id
 * @property integer $service_id
 * @property string $service_sign
 * @property integer $trade_type
 * @property integer $amount
 * @property integer $pre_amount
 * @property integer $post_amount
 * @property string $remark
 * @property integer $created
 */
class AgentAccountTrans extends BaseModel
{

    const TRAN_SETTLE_PASS_INCOME = 'TRAN_SETTLE_PASS_INCOME'; //佣金结算转入
    const TRAN_CASH_PASS_PAY = 'TRAN_CASH_PASS_PAY';//提现
    const TRAN_CASH_FAIL_PAY = 'TRAN_CASH_FAIL_PAY';//提现失败
    public static  function getServiceName(){
        return [
            self::TRAN_SETTLE_PASS_INCOME =>'佣金结算转入',
            self::TRAN_CASH_PASS_PAY =>'提现申请',
            self::TRAN_CASH_FAIL_PAY =>'提现失败',
        ];
    }

    const TRAN_INCOME=1;//收入
    const TRAN_PAY=2;//支出

    public static  function getTradeType(){
        return [
            self::TRAN_INCOME =>'收入',
            self::TRAN_PAY =>'支出',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_account_trans';
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


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'trans_no', 'service_id', 'service_sign', 'trade_type'], 'required'],
            [[ 'agent_id', 'service_id', 'trade_type', 'amount', 'pre_amount', 'post_amount', 'created'], 'integer'],
            [['trans_no'], 'string', 'max' => 200],
            [['service_sign', 'remark'], 'string', 'max' => 50],
            [['trans_no'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trans_no' => 'Trans No',
            'agent_id' => 'Agent ID',
            'service_id' => 'Service ID',
            'service_sign' => 'Service Sign',
            'trade_type' => 'Trade Type',
            'amount' => 'Amount',
            'pre_amount' => 'Pre Amount',
            'post_amount' => 'Post Amount',
            'remark' => 'Remark',
            'created' => 'Created',
        ];
    }

    /**增加流水
     * @param array $params
     * @return array|int
     */
    public static function create($params = [])
    {
        $model = new AgentAccountTrans();
        if ($model->load([$model->formName()=>$params]) && $res = $model->save()) {
            return $res;
        }else{
            return  $model->errors;
        }
    }


    public function getTransList($params, $trade_type=1, $group = ['agent_id'],$limit = 10)
    {
        $child_sql = !empty($group) ? "sum(".self::tableName().".amount) as amount,agent_id ":'*';
        $query = self::find()->select($child_sql);
        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }
        if (isset($params['agent_val']) && $params['agent_val']) {
            $query->andFilterWhere(['agent_id' => $params['agent_val'] ]);
        }
        if (isset($params['service_sign']) && $params['service_sign']) {
            $query->andFilterWhere(['service_sign' => $params['service_sign']]);
        }
        if (!empty($params['date_s']) && !empty($params['date_e'])) {
            $startTime = $params['date_s'];
            $endTime = $params['date_e'];
            $query->andFilterWhere(['between', 'created', $startTime,$endTime]);
        }
        if(!empty($trade_type)){
            $query->andFilterWhere(['trade_type' => $trade_type]);
        }



        if(!empty($group)){
            $query->groupBy($group);
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



    /**交易流水统计
     * @param $params
     * @param int $trade_type
     * @param array $group
     * @return array|\yii\db\ActiveRecord[]
     */

    public function getTransCount($params, $trade_type=1,$status='')
    {
        $child_sql =  self::tableName().".id,sum(".self::tableName().".amount) as amount,".self::tableName().".agent_id ";

        $query = self::find()->select($child_sql);
        if($status!=''){
            $query->innerJoin(AgentApplyCash::tableName(),self::tableName().'.service_id='.AgentApplyCash::tableName().'.id');
        }
        if($status!=''){
            $query->andFilterWhere([AgentApplyCash::tableName().'.status' => $status]);
        }

        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere([self::tableName().'.agent_id' => $params['agent_id']]);
        }
        if (!empty($params['date_s']) && !empty($params['date_e'])) {
            $startTime = $params['date_s'];
            $endTime = $params['date_e'];
            $query->andFilterWhere(['between', self::tableName().'.created', $startTime,$endTime]);
        }

        if(!empty($trade_type)){
            $query->andFilterWhere([self::tableName().'.trade_type' => $trade_type]);
        }

         $query->groupBy(self::tableName().'.agent_id');

        $list=$query->asArray()->all();

        return $list;
    }


    public function getAgentTrans($params)
    {
       // $child_sql = !empty($group) ? "sum(".self::tableName().".amount) as amount,agent_id ":'*';
        $query = self::find()->orderBy('created asc');
        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }
        if (!empty($params['create_time_e']) && !empty($params['create_time_s'])) {
            $startTime = strtotime($params['create_time_s']);
            $endTime = strtotime($params['create_time_e']);
            $query->andFilterWhere(['between', 'created', $startTime,$endTime]);
        }
        $list=$query->asArray()->all();
        return $list;


    }



}
