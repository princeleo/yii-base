<?php

namespace app\models\shop;

use app\models\agent\AgentBase;
use app\models\agent\AgentContract;
use app\models\app\BaseApp;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "shop_base".
 *
 * @property integer $shop_id
 * @property string $name
 * @property string $qq
 * @property integer $agent_id
 * @property integer $pickup_status
 * @property string $brand_label
 * @property string $app_id
 * @property string $contract_no
 * @property string $contract_start
 * @property string $contract_end
 * @property string $tel
 * @property string $website
 * @property string $addr
 * @property string $desc
 * @property string $bg_img
 * @property string $logo
 * @property integer $review_status
 * @property integer $auto_refund
 * @property integer $version
 * @property integer $after_sale_time_status
 * @property integer $after_sale_handle_time
 * @property string $return_address
 * @property string $return_consignee
 * @property string $return_phone
 * @property string $contact
 * @property integer $is_restaurant
 * @property integer $merchant_id
 * @property integer $boss_auto_refund
 * @property integer $shop_limit
 * @property integer $status
 * @property integer $created
 * @property integer $modified
 * @property integer $sync_time
 */
class ShopBase extends \app\models\BaseModel
{

    /**
     * 合同状态
     */
    const CONTRACT_NOT_VALUE = 1;  //未录入
    const CONTRACT_IN_USE = 2;    //生效中
    const CONTRACT_WILL_EXPIRE = 3;  //即将过期
    const CONTRACT_HAVE_EXPIRED = 4;    //已过期
    public static $contractStatus = [
        self::CONTRACT_NOT_VALUE => '未录入',
        self::CONTRACT_IN_USE => '生效中',
        self::CONTRACT_WILL_EXPIRE => '即将过期',
        self::CONTRACT_HAVE_EXPIRED => '已过期',
    ];

    /**
     * 账号状态
     */
    const SHOP_STATUS_DELETED = -1;  //删除
    const SHOP_STATUS_OPEN = 1;  //正常
    const SHOP_STATUS_CLOSED = -2;    //未启用
    public static $shopStatus = [
        self::SHOP_STATUS_OPEN => '正常',
        self::SHOP_STATUS_CLOSED => '未启用'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'agent_id', 'pickup_status', 'review_status', 'auto_refund', 'version', 'after_sale_time_status', 'after_sale_handle_time', 'is_restaurant', 'merchant_id', 'boss_auto_refund', 'shop_limit', 'status', 'created', 'modified', 'sync_time'], 'integer'],
            [['contract_start', 'contract_end'], 'safe'],
            [['desc'], 'string'],
            [['name', 'app_id', 'contract_no', 'return_consignee'], 'string', 'max' => 50],
            [['qq', 'brand_label', 'contact'], 'string', 'max' => 30],
            [['tel', 'return_phone'], 'string', 'max' => 16],
            [['website'], 'string', 'max' => 200],
            [['addr', 'return_address'], 'string', 'max' => 300],
            [['bg_img', 'logo'], 'string', 'max' => 250]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_id' => 'Shop ID',
            'name' => 'Name',
            'qq' => 'Qq',
            'agent_id' => 'Agent ID',
            'pickup_status' => 'Pickup Status',
            'brand_label' => 'Brand Label',
            'app_id' => 'App ID',
            'contract_no' => 'Contract No',
            'contract_start' => 'Contract Start',
            'contract_end' => 'Contract End',
            'tel' => 'Tel',
            'website' => 'Website',
            'addr' => 'Addr',
            'desc' => 'Desc',
            'bg_img' => 'Bg Img',
            'logo' => 'Logo',
            'review_status' => 'Review Status',
            'auto_refund' => 'Auto Refund',
            'version' => 'Version',
            'after_sale_time_status' => 'After Sale Time Status',
            'after_sale_handle_time' => 'After Sale Handle Time',
            'return_address' => 'Return Address',
            'return_consignee' => 'Return Consignee',
            'return_phone' => 'Return Phone',
            'contact' => 'Contact',
            'is_restaurant' => 'Is Restaurant',
            'merchant_id' => 'Merchant ID',
            'boss_auto_refund' => 'Boss Auto Refund',
            'shop_limit' => 'Shop Limit',
            'status' => 'Status',
            'created' => 'Created',
            'modified' => 'Modified',
            'sync_time' => 'Sync Time',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$with=[])
    {
        $query = ShopBase::find()->where(['<>',ShopBase::tableName().'.status',self::SHOP_STATUS_DELETED]);
        if ($with) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ShopBase'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            ShopBase::tableName().'.agent_id' => $this->agent_id,
            ShopBase::tableName().'.shop_id' => $this->shop_id,
        ]);

        if(!empty($params['app_id'])){
            $query->andFilterWhere([ShopBase::tableName().'.app_id'=>$this->app_id]);
        }

        //导出商户数据导出用
        if(!empty($params['shops_id'])) {
            //传过来的是shop_id 的列表字符串   1,2,3,4,5
            $shops_id = explode(',', $params['shops_id']);
            $query->andFilterWhere(['in', ShopBase::tableName().'.shop_id', $shops_id]);
        }

        if(!empty($params['promotion_id'])){
            if($params['promotion_id'] == 'promotion_id'){//暂未分配
                $query->leftJoin(Customer::tableName(),'`'.Customer::tableName().'`.`id` = `'.ShopBase::tableName().'`.`customer_id`');
                $query->andFilterWhere([ Customer::tableName().'.promotion_id'=> 0]);
            }else{
                $query->leftJoin(Customer::tableName(),'`'.Customer::tableName().'`.`id` = `'.ShopBase::tableName().'`.`customer_id`')
                    ->andFilterWhere([ Customer::tableName().'.promotion_id'=> $params['promotion_id']]);
            }
        }


        if(!empty($params['shop_status'])){
            $query->andFilterWhere([ShopBase::tableName().'.status'=>$params['shop_status']]);
        }

        if(!empty($params['contract_status'])){
            switch($params['contract_status']){
                case 1:
                    $query->andFilterWhere([ShopBase::tableName().'.customer_id'=>0]);
                    break;
                case 2:
                    $time = time() + 86400*30;
                    $query->leftJoin(AgentContract::tableName(),'`'.AgentContract::tableName().'`.`customer_id` = `'.ShopBase::tableName().'`.`customer_id`')
                        ->andFilterWhere(['>', AgentContract::tableName().'.end_time', $time]);
                    $query->andFilterWhere(['<>',AgentContract::tableName().'.customer_id',0]);
                    break;
                case 3:
                    $time = time() + 86400*30;
                    $query->leftJoin(AgentContract::tableName(),'`'.AgentContract::tableName().'`.`customer_id` = `'.ShopBase::tableName().'`.`customer_id`')
                        ->andFilterWhere(['>', AgentContract::tableName().'.end_time', time()]);
                    $query->andFilterWhere(['<=',AgentContract::tableName().'.end_time',$time]);
                    $query->andFilterWhere(['<>',AgentContract::tableName().'.customer_id',0]);
                    break;
                case 4:
                    $query->leftJoin(AgentContract::tableName(),'`'.AgentContract::tableName().'`.`customer_id` = `'.ShopBase::tableName().'`.`customer_id`')
                        ->andFilterWhere(['<', AgentContract::tableName().'.end_time', time()]);
                    $query->andFilterWhere(['<>',AgentContract::tableName().'.customer_id',0]);
                    break;
            }
        }

        $query->andFilterWhere(['like', ShopBase::tableName().'.name', $this->name]);
        $query->orderBy(ShopBase::tableName().'.created desc');
//        var_dump($query->createCommand()->getRawSql());exit;
        return $dataProvider;
    }

    public function getAgetShopNums($agent_id)
    {
        $num = $this->find()->where(['agent_id'=>$agent_id,'status'=> self::COL_STATUS_ENABLE])->count();
        return empty($num) ? 0 : $num;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgentBase()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    /**
     * 商户开户详情
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id'])->with('promotion');
    }

    /**
     * 获取合同信息
     * @return \yii\db\ActiveQuery
     */
    public function getContract()
    {
        return $this->hasOne(AgentContract::className(), ['customer_id' => 'customer_id'])->where(['<>','customer_id',0]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(BaseApp::className(), ['app_id' => 'app_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerPaymentSetting()
    {
        return $this->hasOne(CustomerPaymentSetting::className(), ['customer_id' => 'customer_id']);
    }

    /**
     * 统一返回商户列表
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findList($params)
    {
        $query = self::find();

        $query->select([
            "shop_id", "name", "agent_id", "app_id"
        ]);

        if (!empty($params['agent'])) {
            $query->andFilterWhere(["agent_id" => $params['agent']]);
        }

        if (!empty($params['app'])) {
            $query->andFilterWhere(["app_id" => $params['app']]);
        }

        $result = $query->asArray()->all();

        return $result;
    }

    /**
     * 统计服务商下的商户数量
     * @param $agent_id
     */
    public static function countShopByAgent($agent_id){
        if(empty($agent_id)){
            return 0;
        }
        $query = self::find();

        $result = $query->select(["count(shop_id) as shopNum"])->where(['agent_id'=>$agent_id])->asArray()->one();
        if(empty($result['shopNum'])){
            return 0;
        }
        return $result['shopNum'];
    }

    public function getShopList($params,$with=[],$all=false)
    {
        $query = ShopBase::find()->where(['<>','status',self::SHOP_STATUS_DELETED]);
        if ($with) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if(!empty($params['select'])){
            $query->select($params['select']);
        }

        if(!empty($params['ExcludeShop'])){
            $query->andFilterWhere(["not in", ShopBase::tableName().'.shop_id', $params['ExcludeShop']]);
        }

        if(!empty($params['app_id'])){
            $query->andFilterWhere([ShopBase::tableName().'.app_id'=>$params['app_id']]);
        }

        if(!empty($params['shop_name'])){
            $query->andFilterWhere(["like", ShopBase::tableName().'.name', $params['shop_name']]);
        }

        if(!empty($params['agent_id'])){
            $query->andFilterWhere([ShopBase::tableName().'.agent_id'=>$params['agent_id']]);
        }

        if(!empty($params['shop_id'])){
            $query->andFilterWhere([ShopBase::tableName().'.shop_id'=>$params['shop_id']]);
        }

        if(!empty($params['customer_id'])){
            $query->andFilterWhere(['>','customer_id',0]);
        }

//        if(!empty($params['name'])){
//            $query->andFilterWhere(['like',ShopBase::tableName().'.name',$params['name']]);
//        }

        $query->orderBy(ShopBase::tableName().'.created desc');

        if($all){
            return $query->asArray()->all();
        }
        return $dataProvider;
    }


    public function searchByDomain($params)
    {
        $query = ShopBase::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 0,
            ],
        ]);

        if (!($this->load(['ShopBase'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $domainId = !empty($params['domain_id']) ? $params['domain_id'] : false;
        if (!$domainId && \Yii::$app->user->identity->is_root) {
            $agentIds = [];
        } else {
            $agentIds = AgentBase::getIdsByDomain($domainId);
        }
        $query->andFilterWhere(['agent_id' => $agentIds]);

        $query->andFilterWhere([
            'agent_id' => $this->agent_id,
            'shop_id' => $this->shop_id,
        ]);

        if(! empty($params['status'])){
            $query->andFilterWhere(['status' => $this->status]);
        }

        if(!empty($params['app_id'])){
            $query->andFilterWhere(['app_id' => $this->app_id]);
        }
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }

    /**
     * 获取自营商户
     *
     *
     * 自营   单独查 少量商户
     * 非自营  不单独查 大量商户
     * 测试  单独查 少量商户
     * @param $domain_type
     * @return array
     */
    public static function getSelfShopList($domain_type)
    {
        $queryResult = ShopBase::find()
            ->select([ShopBase::tableName().'.shop_id', ShopBase::tableName().'.name'])
            ->leftJoin(AgentBase::tableName(), ShopBase::tableName().'.agent_id'."=".AgentBase::tableName().'.agent_id')
            ->where(['like', AgentBase::tableName().".domain", $domain_type])
            ->all();

        $rs = [];
        foreach ($queryResult as $item) {
            //过滤数据,避免查询出domain为 100,1000 或 340  3400 这种的数据
            if(!in_array($domain_type, explode(',', $item['domain']))) continue;
            $rs[] = ['id' => $item['shop_id'], 'name' => $item['name']];
        }
        return $rs;
    }
}
