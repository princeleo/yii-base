<?php

namespace app\models\shop;

use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\common\exceptions\ParamsException;
use app\models\agent\AgentBase;
use app\models\agent\AgentContract;
use app\models\agent\AgentPromotionAccount;
use app\models\app\BaseApp;
use app\models\baseboss\ShopSalesOrderModel;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\shop\ShopBase;
use app\models\shop\CustomerPaymentSetting;

/**
 * This is the model class for table "consume_order".
 *
 * @property string $id
 * @property integer $consume_date
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $exposure
 * @property integer $click
 * @property integer $ctr
 * @property integer $cpm
 * @property integer $cost
 * @property string $src
 * @property integer $created
 * @property integer $modified
 * @property integer $cpc
 * @property integer $open_order_id
 * @property integer $open_pay_status
 * @property integer $open_order_data
 * @property integer $finance_audit
 * @property integer $version_id
 */
class Customer extends \app\models\BaseModel
{

    const CUSTOMER_HOMEPAGE = "http://www.sctek.com";  //默认网址
    const CUSTOMER_EMAIL = "xuchu@snsshop.cn";  //email
    const CUSTOMER_INDUSTRY = 139;  //默认行业
    /**
     * 商户开通-服务商审核状态
     */
    const CUSTOMER_SHOP_BIG = 1;  //大客户
    const CUSTOMER_SHOP_COMMON = 2;    //普通商户
    const CUSTOMER_SHOP_SUB = 3;  //直营商户
    const CUSTOMER_SHOP_AGENT = 4;    //加盟商户


    /**
     * 商户开通-服务商审核状态
     */
    const CUSTOMER_AGENT_STATUS_DRAFTS = 1;  //草稿
    const CUSTOMER_AGENT_STATUS_SUBMIT = 2;    //待提单
    const CUSTOMER_AGENT_STATUS_REVIEWING = 3;  //待审核
    const CUSTOMER_AGENT_STATUS_REJECT = 4;    //被驳回
    const CUSTOMER_AGENT_STATUS_SUCCESS = 5;  //已开通
    public static $agentStatus = [
        self::CUSTOMER_AGENT_STATUS_DRAFTS => '草稿',
        self::CUSTOMER_AGENT_STATUS_SUBMIT => '待提单',
        self::CUSTOMER_AGENT_STATUS_REVIEWING => '待审核',
        self::CUSTOMER_AGENT_STATUS_REJECT => '被驳回',
        self::CUSTOMER_AGENT_STATUS_SUCCESS => '已开通'
    ];


    /**
     * 商户开通-boss审核状态
     */
    const CUSTOMER_BOSS_REVIEWING = 1;  //待审核
    const CUSTOMER_BOSS_REVIEW_REJECT = 2;    //审核不通过
    const CUSTOMER_BOSS_REVIEW_SUCCESS = 3;    //审核通过
    const CUSTOMER_BOSS_OPEN_SUCCESS = 4;    //开户成功
    public static $reviewStatus = [
        self::CUSTOMER_BOSS_REVIEWING => '待审核',
        self::CUSTOMER_BOSS_REVIEW_REJECT => '审核不通过',
        self::CUSTOMER_BOSS_REVIEW_SUCCESS => '待开通支付',
        self::CUSTOMER_BOSS_OPEN_SUCCESS => '开户成功'
    ];


    /**
     * 结算渠道
     */
    const PAYMENT_BANK_CITIC = 1; //中信银行
    const PAYMENT_BANK_SPD = 2; //浦发银行
    public static $paymentBanks = [
        self::PAYMENT_BANK_CITIC => '中信',
        self::PAYMENT_BANK_SPD => '浦发',
    ];

    /**
     * 账户类型
     */
    const ACCOUNT_TYPE_PUBLIC = 1; //对公账户
    const ACCOUNT_TYPE_PRIVATE = 2; //对私账户
    public static $accountType = [
        self::ACCOUNT_TYPE_PUBLIC => '对公账户',
        self::ACCOUNT_TYPE_PRIVATE => '对私账户',
    ];

    /**
     * 支付账户状态
     */
    const PAYMENT_SETTING_WAIT = 3; //等待开通
    const PAYMENT_SETTING_SUCCESS = 4; //开通成功
    public static $paymentStatus = [
        self::PAYMENT_SETTING_WAIT => '等待开通',
        self::PAYMENT_SETTING_SUCCESS => '开通成功',
    ];

    const OPEN_PAY_NOT_PAY          = 1; // 开户支付未支付
    const OPEN_PAY_PENDING_APPROVAL = 2; // 开户支付待审核
    const OPEN_PAY_COMPLETE         = 3; // 开户支付完成
    const OPEN_PAY_REFUND           = 4; // 开户支付已退款
    public static $OpenPayStatus = [
        self::OPEN_PAY_NOT_PAY          => "未支付",
        self::OPEN_PAY_PENDING_APPROVAL => "待审核",
        self::OPEN_PAY_COMPLETE         => "已支付",
        self::OPEN_PAY_REFUND           => "已退款",
    ];

    const FINANCE_AUDIT_DEFAULT = 1; //待财务审核
    const FINANCE_AUDIT_SUCCESS = 2;//财务审核通过
    const FINANCE_AUDIT_FAIL = 3;//财务审核失败
    public static $financeStatus = [
        self::FINANCE_AUDIT_DEFAULT => '待财务审核',
        self::FINANCE_AUDIT_SUCCESS => '财务审核通过',
        self::FINANCE_AUDIT_FAIL => '财务审核不通过'
    ];


    /**异议审核状态
     * @return array
     */
    public static function getReviewStatus(){
        return [
            self::CUSTOMER_BOSS_REVIEWING => '待审核',
            self::CUSTOMER_BOSS_REVIEW_REJECT => '审核不通过',
            self::CUSTOMER_BOSS_REVIEW_SUCCESS => '审核通过',
            self::CUSTOMER_BOSS_OPEN_SUCCESS => '开户成功'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id','promotion_id','status','review_status','dist_id','city_id','province_id','indu_id','account_type','partner_type','belong_promotion'], 'integer'],
            [['name','short_name','food_business_certificate','door_pic','business_licence_pic','business_licence_no','headman_pic','logo','headman_mobile'
            ,'headman_idnum','headman','legalperson','homepage','mobile','email','address','province_text','city_text','dist_text','bank_cardno','bank',
            'bank_branch','open_account_owner','open_account_mobile','open_account_num','bank_card_pic','bank_code','bank_branch_code','open_account_pic'], 'string'],
            [['app_id','id','no'],'safe'],
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {

        $query = Customer::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['Customer'=>$params]) && $this->validate())) {
        }

        if (isset($params['agent_id'])) {
            $query->andFilterWhere([Customer::tableName().'.agent_id' => $params['agent_id']]);
        }

        if (isset($params['promotion_id'])) {
            $query->andFilterWhere([Customer::tableName().'.promotion_id' => $params['promotion_id']]);
        }

        if (isset($params['promotion_name'])) {
            $query->andFilterWhere(['like',AgentPromotionAccount::tableName().'.true_name', $params['promotion_name']]);
            $query->leftJoin(AgentPromotionAccount::tableName(), Customer::tableName().".promotion_id = ".AgentPromotionAccount::tableName().".id");
        }

        if (!empty($params['review_status'])) {
            $query->andFilterWhere([Customer::tableName().'.review_status' => $params['review_status']]);
        }

        if (!empty($params['customer_id'])) {
            $query->andFilterWhere([Customer::tableName().'.id' => $params['customer_id']]);
        }
        if (!empty($params['status'])) {
            $query->andFilterWhere([Customer::tableName().'.status' => $params['status']]);
        }

        if (!empty($params['no'])) {
            $query->andFilterWhere([Customer::tableName().'.no' => $params['no']]);
        }
        if (!empty($params['name'])) {
            $query->andFilterWhere(['like',Customer::tableName().'.name' , $params['name']]);
        }

        $query->orderBy(Customer::tableName().'.id desc');

        return $dataProvider;
    }



    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function customerSearch($params, $with=[])
    {

        $query = Customer::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['Customer'=>$params]) && $this->validate())) {
        }

        if (isset($params['agent_id'])) {
            $query->andFilterWhere([Customer::tableName().'.agent_id' => $params['agent_id']]);
        }

        if (isset($params['promotion_id'])) {
            $query->andFilterWhere([Customer::tableName().'.promotion_id' => $params['promotion_id']]);
        }

        if (isset($params['promotion_name'])) {
            $query->andFilterWhere(['like',AgentPromotionAccount::tableName().'.true_name', $params['promotion_name']]);
        }

        if (!empty($params['review_status'])) {
            $query->andFilterWhere([Customer::tableName().'.review_status' => $params['review_status']]);
        }else{
            $query->andFilterWhere(['<>',Customer::tableName().'.review_status' ,0]);
        }

        if (!empty($params['customer_id'])) {
            $query->andFilterWhere([Customer::tableName().'.id' => $params['customer_id']]);
        }
        if (!empty($params['no'])) {
            $query->andFilterWhere([Customer::tableName().'.no' => $params['no']]);
        }
        if (!empty($params['status'])) {
            $query->andFilterWhere([Customer::tableName().'.status' => $params['status']]);
        }

        if (!empty($params['name'])) {
            $query->andFilterWhere(['like',Customer::tableName().'.name' , $params['name']]);
        }

        $query->orderBy(Customer::tableName().'.modified desc');

        return $dataProvider;
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function promotionCount($params)
    {
        $query = Customer::find();
        $query->leftJoin('shop_base','shop_base.customer_id=customer.id')->with('shopBase');

        if (isset($params['agent_id'])) {
            $query->andFilterWhere([Customer::tableName().'.agent_id' => $params['agent_id']]);
        }

        if($params['type'] == 1){
            if (isset($params['belong_promotion'])) {
                $query->andFilterWhere([Customer::tableName().'.belong_promotion' => $params['belong_promotion']]);
            }
        }else{
            if (isset($params['promotion_id'])) {
                $query->andFilterWhere([Customer::tableName().'.promotion_id' => $params['promotion_id']]);
            }
        }

        if (!empty($params['start'])) {
            $query->andFilterWhere(['>=',ShopBase::tableName().'.created',$params['start']]);
        }
        if (!empty($params['end'])) {
            $query->andFilterWhere(['<=',ShopBase::tableName().'.created' , $params['end']]);
        }
        if (!empty($params['status'])) {
            $query->andFilterWhere([Customer::tableName().'.status' => $params['status']]);
        }

        return $query->asArray()->all();
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function findCustomer($params, $with=[])
    {
        $query = Customer::find();
        if ($with) {
            $query->with($with);
        }

        $query->andFilterWhere([
            Customer::tableName().'.agent_id' => $params['agent_id'],
            Customer::tableName().'.promotion_id' => $params['promotion_id'],
        ]);
        if (!empty($params['customer_id'])) {
            $query->andFilterWhere([Customer::tableName().'.id' => $params['customer_id']]);
        }
        if (!empty($params['status'])) {
            $query->andFilterWhere([Customer::tableName().'.status' => $params['status']]);
        }

        return $query->one();
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentSetting()
    {
        return $this->hasMany(CustomerPaymentSetting::className(), ['customer_id' => 'id'])->with('bankRates');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAudit()
    {
        return $this->hasMany(CustomerAudit::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentApply()
    {
        return $this->hasOne(CustomerPaymentApply::className(), ['customer_id' => 'id']);
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
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotion()
    {
        return $this->hasOne(AgentPromotionAccount::className(), ['id' => 'promotion_id']);
    }

    public function getAgentBase()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentPromotionAccount()
    {
        return $this->hasOne(AgentPromotionAccount::className(), ['id' => 'promotion_id']);
    }

    public function getAgentContract()
    {
        return $this->hasOne(AgentContract::className(), ['customer_id' => 'id']);
    }

    /**
     * 名称唯一
     * @param $params
     * @return bool
     */
    public function findNameUnique($params){

        $name = isset($params['name'])?$params['name']:"";
        $mobile = isset($params['mobile'])?$params['mobile']:"";

        if(!empty($name) && !empty($mobile)){
            $query = Customer::find()->where("name=:name or mobile=:mobile", [':name' => $name, ':mobile' => $mobile]);
        }elseif(!empty($name) && empty($mobile)){
            $query = Customer::find()->where("name=:name", [':name' => $name]);
        }elseif(empty($name) && !empty($mobile)){
            $query = Customer::find()->where("mobile=:mobile", [':mobile' => $mobile]);
        }else{
            return false;
        }

        if(isset($params['customer_id'])){
            $query->andFilterWhere(['<>',Customer::tableName().'.id', $params['customer_id']]);
        }
        $data = $query->asArray()->one();
        if(empty($data)){
            return false;
        }
        return true;
    }

    public function findModel($id){
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new ParamsException(BaseError::PARAMETER_ERR,['id'=>$id]);
        }
    }

    /**
     * 获取各个审核状态的统计数
     */
    public function countStatus($type ='review_status',$agent_id=""){
        $query = Customer::find();
        if($type == 'review_status'){
            $query->select(['count(id) as num,review_status'])->groupBy('review_status');
        }
        if($type == 'status'){
            $query->select(['count(id) as num,status'])->groupBy('status');
        }
        if(!empty($agent_id)){
            $query->andFilterWhere(['agent_id'=>$agent_id]);
        }
        return $query->asArray()->all();

    }

    public function detail($params){
        $query = Customer::find()->where(['id'=>$params['id']]);
        if(!empty($params['agent_id'])){
            $query->andFilterWhere(['agent_id'=>$params['agent_id']]);
        }
        $data= $query->with(['paymentSetting','app','agentContract','shopBase'])->asArray()->one();
        $data['open_account_pic'] = !empty($data['open_account_pic'])?$this->arrayPicUrl($data['open_account_pic']):[];
        $data['bank_card_pic'] = !empty($data['bank_card_pic'])?$this->arrayPicUrl($data['bank_card_pic']):[];
        $data['headman_pic'] = !empty($data['headman_pic'])?$this->arrayPicUrl($data['headman_pic']):[];
        $data['business_licence_pic'] = !empty($data['business_licence_pic'])?$this->arrayPicUrl($data['business_licence_pic']):[];
        $data['food_business_certificate'] = !empty($data['food_business_certificate'])?$this->arrayPicUrl($data['food_business_certificate']):[];
        $data['door_pic'] = !empty($data['door_pic'])?$this->arrayPicUrl($data['door_pic']):[];
        $data['logo'] = !empty($data['logo'])?$this->arrayPicUrl($data['logo']):[];
        $data['paymentSetting'] = empty($data['paymentSetting']) ? [] : $data['paymentSetting'];
        //处理如果是费率相同的，组合成一条数据
        $arr = [];
        foreach($data['paymentSetting'] as $key =>$value){
            if(in_array($value['rate'], $arr)){
                unset($data['paymentSetting'][$key]);
            } else {
                $arr[] = $value['rate'];
            }
        }
        return $data;
    }

    /**
     * 获取商家信息明细--合并汇率数据
     */
    public function customerArray($id,$setting = false){
        $customer = Customer::find()->where(['id'=>$id])->with(['paymentSetting'])->asArray()->one();
        if(!$setting){
            return $customer;
        }else{
            foreach($customer['paymentSetting'] as $value){
                if($value['rate_type'] == 1){
                    $customer['shop_id'] = $value['shop_id'];
                    $customer['shop_sub_id'] = $value['shop_sub_id'];
                    $customer['rate_table'] = $value['rate'];
                    $customer['payment_type'] = $value['payment_type'];
                    $customer['account'] = $value['account'];
                    $customer['sign_key'] = $value['sign_key'];
                }
                if($value['rate_type'] == 2){
                    $customer['rate_scan'] = $value['rate'];
                }
            }
        }
        unset($customer['paymentSetting']);
        return $customer;
    }

    public function arrayPicUrl($string){
        if(YII_ENV == CODE_RUNTIME_ONLINE){//线上地址转换
            $string = $this->replaceOnlinePic($string);
        }
        $array = array();
        if(!empty($string)){
            $array = explode(',',$string);
            return $array;
        }
        return $array;
    }

    //替换地址
    public function replaceOnlinePic($picUrl){
        if(strstr($picUrl,':81') === false && strstr($picUrl,Yii::$app->params['visitUrl'])){
            return str_replace(Yii::$app->params['visitUrl'],Yii::$app->params['exteriorUrl'],$picUrl);
        }
        return $picUrl;
    }

    public function findOneModel($id){
        $model = Customer::find()->where(['id'=>$id])->with(['paymentSetting','promotion','agentBase'])->one();
        if(empty($model)){
            throw new ApiException(BaseError::SAVE_ERROR);
        }
        return $model;
    }

    /**
     * 获取商家下商户信息
     * @param  $id
     * @return array
     */
    public function getShopInfo($id)
    {
        // 总数
        $query = CustomerPaymentSetting::find()->select([
            "coalesce(count(distinct(".CustomerPaymentSetting::tableName().".shop_id)), 0) shop_number",
            "coalesce(count(distinct(".CustomerPaymentSetting::tableName().".shop_sub_id)), 0) shop_sub_number",
            "coalesce(count(distinct(".CustomerPaymentSetting::tableName().".account)), 0) account_number",
        ])->where([
            "customer_id" => $id
        ]);
//        $sql = $query->createCommand()->getRawSql();
        $data["Number"] = $query->asArray()->one();

        // shop 列表
        $query = CustomerPaymentSetting::find()->select([
            "distinct(".CustomerPaymentSetting::tableName().".shop_id) shop_id",
            "coalesce(".ShopBase::tableName().".name, ".self::tableName().".name) shop_name",
            "coalesce(count(distinct(".CustomerPaymentSetting::tableName().".shop_sub_id)), 0) shop_sub_number",
        ])->where([
            CustomerPaymentSetting::tableName().".customer_id" => $id
        ])->groupBy(CustomerPaymentSetting::tableName().".shop_id")
        ->leftJoin(ShopBase::tableName(), ShopBase::tableName().".shop_id = ".CustomerPaymentSetting::tableName().".shop_id")
        ->leftJoin(self::tableName(), self::tableName().".id = ".CustomerPaymentSetting::tableName().".customer_id");
        $sql = $query->createCommand()->getRawSql();
        $data['shopList'] = CustomerPaymentSetting::findBySql($sql)->asArray()->all();

        // shop_id 数组
        if ($data['shopList']) {
            foreach ($data['shopList'] as $v) {
                $data["shop_id"][] = $v["shop_id"];
            }
        }

        // shop_sub_id 数组
        $query = CustomerPaymentSetting::find()->select([
            "distinct(".CustomerPaymentSetting::tableName().".shop_sub_id) shop_sub_id",
        ])->where([
            "customer_id" => $id
        ]);
        $shop_sub_id = $query->asArray()->all();
        if ($shop_sub_id) {
            foreach ($shop_sub_id as $v) {
                $data["shop_sub_id"][] = $v["shop_sub_id"];
            }
        }

        return $data;
    }


    /**
     * 写入开户订单信息
     * @param $order
     * @return bool
     * */
    public static function _setOpenOrder($order){
        $customer = self::findOne($order['customer_id']);
        $customer->open_order_id = $order['id'];
        $open_order_data['version_id'] = $order["shop_product_version_id"];
        $open_order_data['address'] = $order["address"];
        if (!empty($order["shop_product_hardware_info"])) {
            $i = 0;
            foreach ($order["shop_product_hardware_info"] as $k => $v) {
                $open_order_data['product'][$i]["id"] = $k;
                $open_order_data['product'][$i]["num"] = $v["number"];
            }
        }
        $open_order_data['remark'] = $order["remark"];
        $open_order_data['service']['spec'] = $order["software_service_spec"];
        $open_order_data['service']['val'] = $order["software_service_fee"];
        $customer->open_order_data = json_encode($open_order_data, true);
        $customer->open_pay_status = self::OPEN_PAY_PENDING_APPROVAL;
        if (!$customer->save()) {
            return false;
        }
        return true;
    }

    /*
     *
     * */
    public static function _setOrderId($customer_id, $order_no){
        $customer = Customer::findOne($customer_id);
        $order = ShopSalesOrderModel::findOne(["order_no" => $order_no]);

//        if ((!empty($customer->open_order_id) || $customer->open_pay_status == Customer::OPEN_PAY_COMPLETE) && $order->order_type != ShopSalesOrderModel::OFFLINE_ORDER) {
//            // 已存在订单信息
//            return false;
//        }
//        if ($customer->open_pay_status == Customer::OPEN_PAY_REFUND) {
//            // 已发生退款
//            return false;
//        }

        if ($order->customer_id != $customer->id) {
            return false;   // 信息匹配错误
        }

        $customer->open_order_id = $order->id;
        $customer->open_pay_status = self::OPEN_PAY_PENDING_APPROVAL;
        $customer->finance_audit = self::FINANCE_AUDIT_DEFAULT;
        if (!$customer->save()) {
            return false;   // 保存失败
        }
        return true;
    }
}
