<?php
namespace app\models\shop;

use app\common\services\MessageService;
use app\models\activity\ActivityModel;
use app\models\agent\AgentBase;
use app\models\base\BaseArea;
use app\models\baseboss\ShopProductVersionModel;
use app\models\baseboss\ShopSalesOrderModel;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\app\BaseApp;
use yii\db\Query;

/**
 * This is the model class for table "shop_order".
 *
 * @property integer $id
 * @property string $boss_order_no
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $shop_sub_id
 * @property string $shop_sub_name
 * @property integer $order_id
 * @property string $order_no
 * @property integer $order_type
 * @property integer $uid
 * @property string $nickname
 * @property integer $total_amount
 * @property integer $discount_amount
 * @property integer $delivery_fee
 * @property integer $should_pay
 * @property integer $paid_amount
 * @property integer $refund_amount
 * @property integer $net_amount
 * @property integer $paid_time
 * @property string $trans_id
 * @property integer $pay_status
 * @property integer $order_status
 * @property string $account
 * @property integer $pay_type
 * @property integer $creation_time
 * @property integer $pay_channel
 * @property integer $settlement
 * @property integer $business_type
 * @property integer $rule_id
 * @property string $rate
 * @property string $factorage
 * @property integer $created
 * @property integer $modified
 * @property string $sctek_rate
 * @property string $sctek_cost
 * @property string $sctek_income
 * @property string $sctek_profit
 * @property string $agent_rate
 * @property string $agent_cost
 * @property string $commission
 */
class ShopOrder extends \app\models\BaseModel
{
    /**
     * 订单支付状态
     */
    const ORDER_PAY_STATUS_UNPAID = 1;  //未支付
    const ORDER_PAY_STATUS_PAID = 2;    //已支付
    public static $orderPayStatus = [
        self::ORDER_PAY_STATUS_UNPAID => '未支付',
        self::ORDER_PAY_STATUS_PAID => '已支付'
    ];

    const ORDER_CHANNEL_ZX = 1;     //中信
    const ORDER_CHANNEL_PF = 2;     //浦发
    public static $orderChannels = [
        self::ORDER_CHANNEL_ZX => '中信',
        self::ORDER_CHANNEL_PF => '浦发',
    ];

    /**
     * 订单类型
     */
    const ORDER_TYPE_COMMON = 0;
    const ORDER_TYPE_DIANDAN = 1;
    const ORDER_TYPE_MEMBER_CARD = 2;
    const ORDER_TYPE_SCAN_C2B = 3;
    const ORDER_TYPE_SELF = 4;
    const ORDER_TYPE_SCAN_B2C = 5;
    const ORDER_TYPE_RESERVE = 6;
    const ORDER_TYPE_REFUND = 7;
    const ORDER_TYPE_TABLE = 8;
    public static $orderTypes = [
        self::ORDER_TYPE_DIANDAN => '点单',
        self::ORDER_TYPE_MEMBER_CARD => '会员卡充值',
        self::ORDER_TYPE_SCAN_C2B => '扫码付款',
        self::ORDER_TYPE_SELF => '自助付款',
        self::ORDER_TYPE_SCAN_B2C => '扫码收银',
        self::ORDER_TYPE_RESERVE => '预定订金',
        self::ORDER_TYPE_TABLE => '餐台订单',
        self::ORDER_TYPE_COMMON => '普通订单',
    ];

    /**
     * 订单状态
     */
    const ORDER_STATUS_EXCEPTION = -1;     //未付款
    const ORDER_STATUS_UNPAID = 1;      //未付款
    const ORDER_STATUS_PART_PAID = 2;   //部分付款
    const ORDER_STATUS_PAYING = 3;      //支付中
    const ORDER_STATUS_PAID= 4;         //已付款
    const ORDER_STATUS_DONE= 5;         //交易完成
    const ORDER_STATUS_CANCEL= 6;       //订单已取消
    const ORDER_STATUS_CLOSED= 7;       //订单已关闭


    const ZY_SHOP_DOMAIN_TEST = '34'; //自营商户测试环境
    const ZY_SHOP_DOMAIN_PROD = ''; //自营商户正式环境


    const TYPE_VERSION_KUAI = 1;  //快餐
    const TYPE_VERSION_WEI = 2;  //围餐

    public $date;
    public $eveDate;
    public $weekDate;
    public $monthDate;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','agent_id','rule_id', 'shop_id', 'shop_sub_id', 'order_id', 'order_type', 'uid', 'total_amount', 'discount_amount', 'delivery_fee', 'should_pay', 'paid_amount', 'refund_amount', 'net_amount', 'paid_time', 'pay_status', 'order_status', 'pay_type', 'creation_time', 'pay_channel', 'settlement', 'business_type', 'rule_id', 'created', 'modified'], 'integer'],
            [['rate', 'factorage', 'sctek_rate', 'sctek_cost', 'sctek_income', 'sctek_profit', 'agent_rate', 'agent_cost', 'commission'], 'number'],
            [['boss_order_no', 'trans_id'], 'string', 'max' => 64],
            [['app_id', 'order_no'], 'string', 'max' => 50],
            [['shop_sub_name'], 'string', 'max' => 255],
            [['nickname'], 'string', 'max' => 100],
            [['account'], 'string', 'max' => 32],
            [['boss_order_no'], 'unique'],
            [['order_no', 'app_id'], 'unique', 'targetAttribute' => ['order_no', 'app_id'], 'message' => 'The combination of App ID and Order No has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'boss_order_no' => 'Boss Order No',
            'app_id' => 'App ID',
            'agent_id' => 'Agent ID',
            'shop_id' => 'Shop ID',
            'shop_sub_id' => 'Shop Sub ID',
            'shop_sub_name' => 'Shop Sub Name',
            'order_id' => 'Order ID',
            'order_no' => 'Order No',
            'order_type' => 'Order Type',
            'uid' => 'Uid',
            'nickname' => 'Nickname',
            'total_amount' => 'Total Amount',
            'discount_amount' => 'Discount Amount',
            'delivery_fee' => 'Delivery Fee',
            'should_pay' => 'Should Pay',
            'paid_amount' => 'Paid Amount',
            'refund_amount' => 'Refund Amount',
            'net_amount' => 'Net Amount',
            'paid_time' => 'Paid Time',
            'trans_id' => 'Trans ID',
            'pay_status' => 'Pay Status',
            'order_status' => 'Order Status',
            'account' => 'Account',
            'pay_type' => 'Pay Type',
            'creation_time' => 'Creation Time',
            'pay_channel' => 'Pay Channel',
            'settlement' => 'Settlement',
            'business_type' => 'Business Type',
            'rule_id' => 'Rule ID',
            'rate' => 'Rate',
            'factorage' => 'Factorage',
            'created' => 'Created',
            'modified' => 'Modified',
            'sctek_rate' => 'Sctek Rate',
            'sctek_cost' => 'Sctek Cost',
            'sctek_income' => 'Sctek Income',
            'sctek_profit' => 'Sctek Profit',
            'agent_rate' => 'Agent Rate',
            'agent_cost' => 'Agent Cost',
            'commission' => 'Commission',
        ];
    }

    /**
     * @param $params
     * @param $with
     * @return array|null|\yii\db\ActiveRecord
     */
    public function detail($params, $with)
    {
        $query = ShopOrder::find();
        if ($with) {
            $query->with($with);
        }
        if (!($this->load(['ShopOrder'=>$params]) && $this->validate())) {
            return [];
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'app_id' => $this->app_id,
            'shop_id' => $this->shop_id,
        ]);
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(), ShopBase::tableName() . ".account_name like '%{$params['shop_name']}%'");
        }

        return $query->asArray()->one();
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ShopOrder::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ShopOrder'=>$params]) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            ShopOrder::tableName().'.id' => $this->id,
            ShopOrder::tableName().'.agent_id' => $this->agent_id,
            ShopOrder::tableName().'.app_id' => $this->app_id,
            ShopOrder::tableName().'.shop_id' => $this->shop_id,
            ShopOrder::tableName().'.shop_sub_id' => $this->shop_sub_id,
            ShopOrder::tableName().'.pay_status' => $this->pay_status,
        ]);
        if (!empty($params['paid_time_s'])) {
            $query->andFilterWhere(['>=', ShopOrder::tableName().'.paid_time', $params['paid_time_s']]);
        }
        if (!empty($params['paid_time_e'])) {
            $query->andFilterWhere(['<', ShopOrder::tableName().'.paid_time', $params['paid_time_e']]);
        }

        if (!empty($params['order_no'])) {
            $query->andFilterWhere(['like', ShopOrder::tableName().'.order_no', $params['order_no']]);
        }

        if (!empty($params['creation_time_s'])) {
            $query->andFilterWhere(['>=', ShopOrder::tableName().'.creation_time', $params['creation_time_s']]);
        }
        if (!empty($params['creation_time_e'])) {
            $query->andFilterWhere(['<', ShopOrder::tableName().'.creation_time', $params['creation_time_e']]);
        }
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(),'`'.ShopOrder::tableName().'`.`shop_id` = `'.ShopBase::tableName().'`.`shop_id`')
                ->andFilterWhere(['like', ShopBase::tableName().'.name', $params['shop_name']]);
        }
        if(!empty($params['shop_sub_name'])) {
            $query->andFilterWhere(['like', ShopOrder::tableName().'.shop_sub_name', $params['shop_sub_name']]);
        }
        $query->andFilterWhere(['>=', ShopOrder::tableName().'.paid_amount', 0]);
        if (!empty($params['sort'])) {
            $query->orderBy($params['sort']);
        }

        if (!empty($params['activity_name'])) {
            $query->andFilterWhere(['like',ActivityModel::tableName().'.name', $params['activity_name']]);
            $query->leftJoin(ActivityModel::tableName(), ShopOrder::tableName().".gateway_activity_id = ".ActivityModel::tableName().".gateway_activity_id");
        }

        if (!empty($params['activity_id'])) {
            $query->andFilterWhere([ActivityModel::tableName().'.id' => $params['activity_id']]);
            $query->leftJoin(ActivityModel::tableName(), ShopOrder::tableName().".gateway_activity_id = ".ActivityModel::tableName().".gateway_activity_id");
        }

        $query->orderBy(ShopOrder::tableName().'.paid_time desc');
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
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(ActivityModel::className(), ['gateway_activity_id' => 'gateway_activity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderDetail()
    {
        return $this->hasOne(ShopOrderDetail::className(), ['boss_order_no' => 'boss_order_no']);
    }

    public function getSummary($params)
    {
        $query = ShopOrder::find();
        if (!empty($params['selectStr'])) {
            $query->select($params['selectStr']);
        } else {
            $query->select('count(order_no) as iOrderCount, sum(paid_amount) as iOrderAmount');
        }

        $query->andFilterWhere([
            ShopOrder::tableName().'.id' => isset($params['id']) ? $params['id'] : null,
            ShopOrder::tableName().'.agent_id' => isset($params['agent_id']) ? $params['agent_id'] : null,
            ShopOrder::tableName().'.app_id' => isset($params['app_id']) ? $params['app_id'] : null,
            ShopOrder::tableName().'.shop_id' => isset($params['shop_id']) ? $params['shop_id'] : null,
            ShopOrder::tableName().'.shop_sub_id' => isset($params['shop_sub_id']) ? $params['shop_sub_id'] : null,
            ShopOrder::tableName().'.pay_status' => isset($params['pay_status']) ? $params['pay_status'] : null,
        ]);
        if (!empty($params['paid_time_s'])) {
            $query->andFilterWhere(['>=', ShopOrder::tableName().'.paid_time', $params['paid_time_s']]);
        }
        if (!empty($params['paid_time_e'])) {
            $query->andFilterWhere(['<', ShopOrder::tableName().'.paid_time', $params['paid_time_e']]);
        }

        if (!empty($params['order_no'])) {
            $query->andFilterWhere(['like', ShopOrder::tableName().'.order_no', $params['order_no']]);
        }

        if (!empty($params['creation_time_s'])) {
            $query->andFilterWhere(['>=', ShopOrder::tableName().'.creation_time', $params['creation_time_s']]);
        }
        if (!empty($params['creation_time_e'])) {
            $query->andFilterWhere(['<', ShopOrder::tableName().'.creation_time', $params['creation_time_e']]);
        }
        $query->andFilterWhere(['>=', ShopOrder::tableName().'.paid_amount', 0]);

        return $query->asArray()->one();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(BaseApp::tableName(), ['app_id' => 'app_id']);
    }


    /**
     * 活动订单统计按天
     * @param $params
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getSummaryByActivity($params)
    {
        $query = ShopOrder::find()->select([
            'count(DISTINCT(shop_id)) as shop_num',
            'count(uid) as uid_num',
            'count(order_no) as order_num',
            'sum(subsidy_total_amount) as subsidy_total_amount',
            'FROM_UNIXTIME(paid_time, "%Y-%m-%d") as date'
        ]);

        $query->andFilterWhere([
            ShopOrder::tableName().'.gateway_activity_id' => isset($params['gateway_activity_id']) ? $params['gateway_activity_id'] : null,
        ]);

        $query->groupBy(['FROM_UNIXTIME(paid_time, "%Y-%m-%d")']);

        return $query->asArray()->all();
    }

    /**
     * 活动订单统计全部
     * @param $params
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getActivitySumAll($params)
    {
        $query = ShopOrder::find()->select([
            'count(DISTINCT(shop_id)) as shop_num',
            'count(DISTINCT(uid)) as uid_num',
            'count(order_no) as order_num',
            'sum(subsidy_total_amount) as subsidy_total_amount',
        ]);

        $query->andFilterWhere([
            ShopOrder::tableName().'.gateway_activity_id' => isset($params['gateway_activity_id']) ? $params['gateway_activity_id'] : null,
        ]);

        return $query->asArray()->all();
    }

    /**
     * 大象点餐 - 查询商户交易额及订单数量
     * @param array $shop_ids 商户ID
     * @param array $times 查询时间段
     * @return array|bool
     */
    public function sysOrders(array $shop_ids, array $times=[],  $addition) {
        if(!$shop_ids) return [];
        if(!$times) {
            $start_time = strtotime(date('Y-m-d 00:00:00', strtotime('-1 days')));
            $end_time = strtotime(date('Y-m-d 23:59:59', strtotime('-1 days')));
        } else {
            $start_time = $times['start_time'];
            $end_time   = $times['end_time'];
        }

        $query = ShopOrder::find();
        $select = ['COUNT(id) AS order_count','SUM(paid_amount) AS all_payed'];

        $query->select($select)
            ->where(['shop_id' => $shop_ids])
            ->andWhere(['version_type' => $addition['$addition']])
            ->andWhere("paid_time >='{$start_time}' AND paid_time <='{$end_time}'");
        //是否是扫码点餐订单，否则就查询全部订单
        if(isset($addition['is_scan_order']) && $addition['is_scan_order']) {
            $query->andWhere(['order_type' => self::ORDER_TYPE_COMMON]);
        }
        if(isset($addition['type_version']) && $addition['type_version'] == self::TYPE_VERSION_WEI) {
            //围餐
            $query->andWhere(['type_version' => self::TYPE_VERSION_WEI]);
        } else {
            //快餐
            $query->andWhere(['type_version' => self::TYPE_VERSION_KUAI]);
        }
        //达标订单数，实际支付 大于等于5 RMB
        if(isset($addition['pass_order']) && $addition['pass_order']) {
            $query->andWhere('paid_amount >= 500');
        }
        $res = $query->one();
        return $res;
    }

    /**
     * 服务商TOP排行榜
     * @param int $top
     * @return array|\yii\db\ActiveRecord[]
     */
    public function agentTop($top = 20) {

//        SELECT shop.agent_id, agent.agent_name, COUNT(_order.boss_order_no) AS order_count, SUM(_order.paid_amount) AS all_payed
//          FROM shop_order AS _order
//LEFT JOIN shop_base AS shop ON _order.shop_id = shop.shop_id
//LEFT JOIN agent_base AS agent ON shop.agent_id = agent.agent_id
//WHERE version_type = 0
//GROUP BY shop.agent_id
//ORDER BY all_payed DESC
        $start_time = $this->date['start_time'];
        $end_time = $this->date['end_time'];
        $query = new Query();
        $res   = $query->from(ShopOrder::tableName())
            ->select([
                ShopBase::tableName().'.agent_id',
                AgentBase::tableName().'.agent_name',
                "COUNT(".ShopOrder::tableName().".id) AS order_count",
                "SUM(".ShopOrder::tableName().".paid_amount) AS all_paid"])
            ->leftJoin(ShopBase::tableName(), ShopOrder::tableName().'.shop_id='.ShopBase::tableName().'.shop_id')
            ->leftJoin(AgentBase::tableName(), AgentBase::tableName().'.agent_id='.ShopBase::tableName().'.agent_id')
            ->where([
                ShopOrder::tableName().'.order_type' => self::ORDER_TYPE_COMMON]
            )
            ->andWhere("paid_time >='{$start_time}' AND paid_time <='{$end_time}'")
            ->groupBy(ShopBase::tableName().'.agent_id')
            ->orderBy('all_paid DESC')
            ->limit($top)
            ->all();
//        print_r($query->createCommand()->getRawSql());exit;
        return $res;
    }

    /**
     * 商户扫码点餐销售额TOP N
     * @param int $top
     * @return array
     */
    public function shopTop($top = 10) {
        $start_time = $this->date['start_time'];
        $end_time = $this->date['end_time'];
        //获取所有自营商户
        $query = new Query();
        return $query->from(ShopOrder::tableName())
            ->select([
                ShopBase::tableName().'.name',
                "COUNT(".ShopOrder::tableName().".boss_order_no) AS order_count",
                "SUM(".ShopOrder::tableName().".paid_amount) AS all_paid"])
            ->leftJoin(ShopBase::tableName(), ShopOrder::tableName().'.shop_id='.ShopBase::tableName().'.shop_id')
            ->where([
                ShopOrder::tableName().'.order_type' => self::ORDER_TYPE_COMMON,
                ShopOrder::tableName().'.shop_type' => 1,
            ])
            ->andWhere("paid_time >='{$start_time}' AND paid_time <='{$end_time}'")
            ->groupBy(ShopOrder::tableName().'.shop_id')
            ->orderBy('all_paid DESC')
            ->limit($top)
            ->all();
    }

    /**
     * 获取时间段前多少名城市交易额
     * @param int $top
     * @return array  [['city' => '深圳市', 'count' => 10, 'payed' => 116],...]
     */
    public function mapByCity($top = 20)
    {
        //获取昨日商户交易额，按照商户分组
        $data = $this->dataGroupByMerchant();
        //获取商户对应的城市
        $merchantCity = $this->getCity(array_column($data, 'shop_id'));
        $shopCity = $this->mapMerchantCity($merchantCity);
        $cityPayed = [];
        //城市数据累加统计
        foreach ($data as $shop) {
            $city = $shopCity[$shop['shop_id']];//城市名称
            if (!isset($cityPayed[$city])) {
                $cityPayed[$city] = ['city' => $city, 'count' => 0, 'paid' => 0];
            }

            $cityPayed[$city]['count'] += $shop['order_count'];
            $cityPayed[$city]['paid'] += $shop['all_paid'];
        }
        //交易额倒序排序
        $cityPayed = array_values($cityPayed);
        uasort($cityPayed, function ($a, $b) {
            return $a['paid'] > $b['paid'];
        });
        //取TOP个商户数据
        return array_slice($cityPayed, 0, $top - 1);
    }

    /**
     * 每日交易额按商户分组
     * @return array
     */
    public function dataGroupByMerchant() {
        $start_time = $this->date['start_time'];
        $end_time = $this->date['end_time'];

        $query = new Query();
        $rs = $query->from(ShopOrder::tableName())->select(['shop_id', 'count(id) AS order_count', 'SUM(paid_amount) AS all_paid'])
            ->from(self::tableName())
            ->where([
                    ShopOrder::tableName().'.version_type' => [0,1],
                    ShopOrder::tableName().'.order_type' => self::ORDER_TYPE_COMMON]
            )
            ->andWhere("paid_time >='{$start_time}' AND paid_time <='{$end_time}'")
            ->groupBy('shop_id')->all();
        return $rs;
    }

    /**
     * 获取商户对应的城市名称
     * @param array $shopsId 商户ID
     * @return array
     */
    public function getCity($shopsId=[]) {
        if(!$shopsId) return [];
        $query = new Query();
        $rs = $query->from(ShopBase::tableName())->select([BaseArea::tableName().'.name AS name', ShopBase::tableName().'.shop_id AS shop_id'])
            ->leftJoin(Customer::tableName(), ShopBase::tableName().'.customer_id ='.Customer::tableName().'.id')
            ->leftJoin(BaseArea::tableName(), Customer::tableName().'.city_id = '.BaseArea::tableName().'.id')
            ->where([
                ShopBase::tableName().'.shop_id' => $shopsId
            ])
            ->all();
        return $rs;
    }

    /**
     * 商户城市数据处理成键值对，[[商户ID=>城市]，...]
     * @param $data
     * @return array
     */
    public function mapMerchantCity($data) {
        $res = [];
        if(!$data) return [];
        foreach ($data as $merchant) {
            $shopId = $merchant['shop_id'];
            $city = $merchant['name'];
            $res[$shopId] = $city;
        }
        return $res;
    }

    /**
     * 套餐销售数据
     * @return array
     */
    public function orderSale($times) {

        $start_time = $times['start_time'];
        $end_time   = $times['end_time'];

        //查询昨日销售套餐数据
        $query = new Query();
        $res = $query->from(ShopSalesOrderModel::tableName())
            ->select([
                ShopProductVersionModel::tableName().'.id',
                ShopProductVersionModel::tableName().'.name',
                'count('.ShopSalesOrderModel::tableName().'.id'.') as order_count',  //销售套餐数
                'sum('.ShopSalesOrderModel::tableName().'.setup_fee'.') as setup_fee', //开户费
                'sum('.ShopSalesOrderModel::tableName().'.software_service_fee'.') as service_fee', //平台服务费
                'sum('.ShopSalesOrderModel::tableName().'.hardware_purchase_cost'.') as hardware_fee', //硬件费用
                'sum('.ShopSalesOrderModel::tableName().'.total_order_amount'.') as total_amount' //该类型套餐全部销售额
                ]
            )
            ->where([
                'pay_status' => ShopSalesOrderModel::PAY_STATUS_SUCCEED, //已支付
            ])
            ->leftJoin(ShopProductVersionModel::tableName(), ShopProductVersionModel::tableName().'.id='.ShopSalesOrderModel::tableName().'.shop_product_version_id')
            ->andWhere(ShopSalesOrderModel::tableName().".pay_time >='{$start_time}' AND ".ShopSalesOrderModel::tableName().".pay_time <='{$end_time}'")
            ->groupBy(ShopSalesOrderModel::tableName().'.shop_product_version_id')
            ->indexBy('id')
            ->all();
        return $res;
    }




    /**
     * 服务商套餐销售排行榜
     * @param int $top
     * @return array
     */
    public function saleOrderTopN($top = 20)
    {
        $start_time = $this->date['start_time'];
        $end_time = $this->date['end_time'];

        //查询昨日销售套餐数据
        $query = new Query();
        $res = $query->from(ShopSalesOrderModel::tableName())
            ->select([
                AgentBase::tableName().'.agent_name',
                'count('.ShopSalesOrderModel::tableName().'.id'.') as order_count',  //销售套餐数
                'sum('.ShopSalesOrderModel::tableName().'.setup_fee'.') as setup_fee',  //开户费
                'count('.ShopSalesOrderModel::tableName().'.software_service_fee'.') as software_service_fee',  //软件服务费
                'count('.ShopSalesOrderModel::tableName().'.hardware_purchase_cost'.') as hardware_purchase_cost',  //硬件费用
                'sum('.ShopSalesOrderModel::tableName().'.total_order_amount'.') as total_amount', //该类型套餐全部销售额
            ])
            ->where([
                'pay_status' => ShopSalesOrderModel::PAY_STATUS_SUCCEED, //已支付
            ])
            ->leftJoin(AgentBase::tableName(), AgentBase::tableName().'.agent_id='.ShopSalesOrderModel::tableName().'.agent_id')
            ->andWhere(ShopSalesOrderModel::tableName().".pay_time >='{$start_time}' AND ".ShopSalesOrderModel::tableName().".pay_time <='{$end_time}'")
            ->groupBy(ShopSalesOrderModel::tableName().'.agent_id')
            ->orderBy('total_amount DESC')
            ->limit($top)
            ->all();
        return $res;
    }

    /**
     * 查询所有启用状态的套餐数据
     * @return array|\yii\db\ActiveRecord[]
     */
    public function allShopProdVersion()
    {
        return ShopProductVersionModel::find()->select(['id', 'name'])
            ->where(['status' => ShopProductVersionModel::STATUS_DEFAULT])->asArray()->all();

    }

    public function orders($commonOrder = FALSE, $passOrder = FALSE, $versionType = 1)
    {
        //案例 SQL语句
//        SELECT FROM_UNIXTIME(paid_time,  '%Y-%m-%d'), shop_type, COUNT(id)
//        FROM shop_order
//        WHERE FROM_UNIXTIME(paid_time,  '%Y-%m-%d' ) IN ('2017-10-17')  AND shop_type IN (1,2,3) AND version_type = 1
//        GROUP BY FROM_UNIXTIME(paid_time,  '%Y-%m-%d' ),shop_type

        $times = ['last' => '', 'eve' => '', 'lastWeek' => '', 'lastMonth' => ''];
        $times['last']      = date('Ymd', $this->date['start_time']); //昨日
        $times['eve']       = date('Ymd', $this->eveDate['start_time']); //前日
        $times['lastWeek']  = date('Ymd', $this->weekDate['start_time']); //一周前同期
        $times['lastMonth'] = date('Ymd', $this->monthDate['start_time']); //四周前同期

        $where = [
            "FROM_UNIXTIME(paid_time,  '%Y%m%d' )" => array_values($times),
            'shop_type' => [1,2,3],
            'version_type' => $versionType
        ];
        //是否是扫码点餐订单
        if($commonOrder) {
            $where['order_type'] = ShopOrder::ORDER_TYPE_COMMON;
        }

        $passWhere = [];
        if($passOrder) {
            $passWhere = ['>=','paid_amount',500];
        }
        $query = new Query();
        $data = $query->from(ShopOrder::tableName())
            ->select(["FROM_UNIXTIME(paid_time,  '%Y%m%d') as _date", 'shop_type', 'COUNT(id) as order_count', 'sum(paid_amount) as all_paid'])
            ->where($where)
            ->andWhere($passWhere)
            ->groupBy(["FROM_UNIXTIME(paid_time,  '%Y%m%d' )", "shop_type"])
            ->all();
//        print_r($query->createCommand()->getRawSql());
//        var_dump($data);
        return compact('times', 'data');
    }

    /**
     * 获取月达标订单数
     * @param $version
     * @return int
     */
    public function passOrderCount($version) {
        $startTime = strtotime(date('Y-m-01 00:00:00', $this->date['start_time']));
        $endTime   = strtotime(date('Y-m-d 23:59:59', strtotime("$startTime +1 month -1 day")));

        $query = new Query();
        $rs = $query->from(ShopOrder::tableName())
            ->select(['COUNT(id) as order_count'])
            ->Where(ShopOrder::tableName().".paid_time >='{$startTime}' AND ".ShopOrder::tableName().".paid_time <='{$endTime}'")
            ->andWhere(['version_type' => $version])
            ->andWhere("paid_amount >= 500")
            ->one();
        return $rs['order_count'];
    }
}
