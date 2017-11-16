<?php

namespace app\models\agent;

use app\common\helpers\ConstantHelper;
use app\models\app\BaseApp;
use app\models\shop\ShopBase;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_settle_report".
 *
 * @property integer $id
 * @property integer $start_time
 * @property integer $end_time
 * @property string $key_date
 * @property integer $agent_id
 * @property string $app_id
 * @property integer $rules_id
 * @property integer $shop_id
 * @property integer $shop_sub_id
 * @property string $shop_sub_name
 * @property integer $app_type
 * @property string $item_name
 * @property integer $commission_rate
 * @property integer $play_agent_money
 * @property integer $trade_money
 * @property integer $created
 * @property integer $modified
 */
class AgentSettleReport extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_settle_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'agent_id', 'rule_id', 'app_type', 'shop_id', 'business_type', 'created', 'modified','pay_channel'], 'integer'],
            [['settle_amount', 'pay_amount', 'refund_amount', 'rate', 'factorage', 'sctek_rate', 'sctek_cost', 'sctek_income', 'sctek_profit', 'agent_rate', 'agent_cost', 'commission'], 'number'],
            [['key_date', 'app_id', 'group_name'], 'string', 'max' => 50],
            [['pay_account'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key_date' => 'Key Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'settle_amount' => 'Settle Amount',
            'pay_amount' => 'Pay Amount',
            'refund_amount' => 'Refund Amount',
            'agent_id' => 'Agent ID',
            'app_id' => 'App ID',
            'rule_id' => 'Rule ID',
            'app_type' => 'Rule Type',
            'shop_id' => 'Shop ID',
            'business_type' => 'Business Type',
            'pay_account' => 'Pay Account',
            'group_name' => 'Group Name',
            'rate' => 'Rate',
            'factorage' => 'Factorage',
            'sctek_rate' => 'Sctek Rate',
            'sctek_cost' => 'Sctek Cost',
            'sctek_income' => 'Sctek Income',
            'sctek_profit' => 'Sctek Profit',
            'agent_rate' => 'Agent Rate',
            'agent_cost' => 'Agent Cost',
            'commission' => 'Commission',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * 分佣类型
     */
    const APP_TYPE_ORDER_COMMISSION = 1; //流水分页
    const APP_TYPE_CONSUME_COMMISSION = 2; //消费分佣

    /**分佣类型
     * @return array
     */
    public static function getApp_type(){
        return [
            self::APP_TYPE_ORDER_COMMISSION => '流水分佣',
            self::APP_TYPE_CONSUME_COMMISSION => '消费分佣',
        ];
    }

    /***
     * @var array
     */
    public static $platform = [
        ConstantHelper::PLATFORM_ZHCT,
        ConstantHelper::PLATFORM_VIKDUO,
    ];




    /**
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
    }


    /**根据结算申请id 获取月表报数据
     * @param int $audit_id
     * @param $key_date
     * @param int $agent_id
     * @return string
     */
    public static function getAuditReportList($audit_id=0,$key_date,$agent_id=0,$group = ['app_type','rule_id'])
    {
        $child_sql = !empty($group) ? "sum(".self::tableName().".commission) as commission,sum(".self::tableName().".settle_amount) as settle_amount," : self::tableName().".commission as commission,".self::tableName().".settle_amount as settle_amount,";
        $query = self::find()->select(self::tableName().".id,".self::tableName().".start_time,".self::tableName().".end_time,".self::tableName().".key_date,
        ".self::tableName().".agent_id,".self::tableName().".app_id,".self::tableName().".rule_id,".self::tableName().".shop_id,".self::tableName().".group_name,
        ".self::tableName().".app_type,".self::tableName().".agent_rate,".self::tableName().".business_type,".self::tableName().".pay_account,".self::tableName().".pay_channel,".self::tableName().".pay_amount,".self::tableName().".refund_amount,".self::tableName().".settle_amount,
        ".self::tableName().".rate,".self::tableName().".factorage,".self::tableName().".sctek_rate,".self::tableName().".sctek_cost,".self::tableName().".sctek_income,".self::tableName().".agent_cost,".self::tableName().".agent_rate,".self::tableName().".commission,".self::tableName().".sctek_profit,
        ".ShopBase::tableName().".name as shop_name,
        ".AgentSettleMonth::tableName().".play_agent_money ,".$child_sql."
        ".self::tableName().".created,".self::tableName().".modified")
            ->leftJoin(AgentSettleMonth::tableName(), AgentSettleMonth::tableName() . '.key_date=' . self::tableName() . '.key_date AND '.AgentSettleMonth::tableName().'.agent_id = '.self::tableName().'.agent_id')
            ->leftJoin(ShopBase::tableName(), ShopBase::tableName() . '.shop_id=' . self::tableName() . '.shop_id');
        if ($audit_id>0) {
            $query->where([AgentSettleMonth::tableName().'.id'=>$audit_id]);
        }
        if (!empty($key_date)) {
            $query->andWhere([self::tableName().'.key_date'=>$key_date]);
        }
        if ($agent_id>0) {
            $query->andWhere([self::tableName().'.agent_id'=>$agent_id]);
        }
        if(!empty($group)){
            $query->groupBy(['app_type','rule_id']);
        }
        //pr($query->createCommand()->getRawSql());die;
        $list=$query->asArray()->all();
        $array=[];
        $total_key=0;//总数量
            foreach ($list as $key => $value) {
                $array[$total_key] = $value;
                    $total_key++;
            }
        if(!empty($array))
        {
            foreach ($array as $key => $value) {
                $array[$key]=$value;
                $array[$key]['app_name'] = AgentSettleReport::getApp_id()[$value['app_id']];
                $array[$key]['type_name'] = AgentSettleReport::getApp_type()[$value['app_type']];
                $array[$key]['total_key'] = $total_key;
                $list = ShopBase::find()->select('name')->where(['shop_id'=>$value['shop_id']])->one();
                if(!empty($list)) {
                    $shop_name=$list['name'];
                }else{
                    $shop_name='';
                }

                $array[$key]['shop_name'] = $shop_name;


            }
        }



        return $array;
    }







    /**平台集合
     * @return array
     */
    public static  function getApp_id(){

        $query=    BaseApp::find();
        $query=$query->asArray()->all();
        $arr=array();

        foreach ($query as $key=>$value)
        {
            $arr[$value['app_id']]=$value['app_name'];
        }
        return $arr;
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = AgentSettleReport::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['AgentSettleReport'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'settle_amount' => $this->settle_amount,
            'pay_amount' => $this->pay_amount,
            'refund_amount' => $this->refund_amount,
            'agent_id' => $this->agent_id,
            'rule_id' => $this->rule_id,
            'app_type' => $this->app_type,
            'shop_id' => $this->shop_id,
            'business_type' => $this->business_type,
            'pay_channel' => $this->pay_channel,
            'rate' => $this->rate,
            'factorage' => $this->factorage,
            'sctek_rate' => $this->sctek_rate,
            'sctek_cost' => $this->sctek_cost,
            'sctek_income' => $this->sctek_income,
            'sctek_profit' => $this->sctek_profit,
            'agent_rate' => $this->agent_rate,
            'agent_cost' => $this->agent_cost,
            'commission' => $this->commission,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        $query->andFilterWhere(['like', 'key_date', $this->key_date])
            ->andFilterWhere(['like', 'app_id', $this->app_id])
            ->andFilterWhere(['like', 'pay_account', $this->pay_account])
            ->andFilterWhere(['like', 'group_name', $this->group_name]);

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['shop_id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgentBase()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }


    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function SearchReport($params, $with=[])
    {
        $query = AgentSettleReport::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['AgentSettleReport'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'settle_amount' => $this->settle_amount,
            'pay_amount' => $this->pay_amount,
            'refund_amount' => $this->refund_amount,
            'agent_id' => $this->agent_id,
            'rule_id' => $this->rule_id,
            'app_type' => $this->app_type,
            'shop_id' => $this->shop_id,
            'business_type' => $this->business_type,
            'pay_channel' => $this->pay_channel,
            'rate' => $this->rate,
            'factorage' => $this->factorage,
            'sctek_rate' => $this->sctek_rate,
            'sctek_cost' => $this->sctek_cost,
            'sctek_income' => $this->sctek_income,
            'sctek_profit' => $this->sctek_profit,
            'agent_rate' => $this->agent_rate,
            'agent_cost' => $this->agent_cost,
            'commission' => $this->commission,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        $query->andFilterWhere(['>=', 'start_time', $this->start_time])
            ->andFilterWhere(['<=', 'end_time', $this->end_time]);

        return $dataProvider;
    }




}
