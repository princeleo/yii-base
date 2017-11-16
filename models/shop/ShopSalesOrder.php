<?php

/**
 * Created by PhpStorm.
 * User: ShiYaoJia
 * Date: 2017/08/24
 * Time: 16:50
 */

namespace app\models\shop;

use app\models\agent\AgentBase;
use app\models\agent\AgentPromotionAccount;
use app\models\app\BaseApp;
use Yii;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "sales_order".
 *
 * @property integer    $id
 * @property string     $mobile                     下单手机（当前Customer关联mobile）
 * @property integer    $app_id                     平台id
 * @property integer    $agent_id                   服务商id
 * @property integer    $customer_id                商家id
 * @property integer    $promotion_id               地推员id
 * @property integer    $shop_id                    商户id
 * @property string     $order_no                   订单号（唯一索引）
 * @property string     $transaction_id             威富通订单号
 * @property string     $out_transaction_id         第三方订单号
 * @property integer    $shop_product_version_id   （产品套餐/版本表）id
 * @property integer    $setup_fee                  开户费(分)
 * @property integer    $software_service_fee       软件服务费(分)
 * @property integer    $hardware_purchase_cost     硬件成本费(分)
 * @property integer    $total_order_amount         订单总额（开户+软件服务+硬件成本）（分）
 * @property integer    $actual_amount              实际付款（分）
 * @property string     $shop_product_hardware_info 购买硬件详情
 * @property integer    $software_service_spec      软件服务规格（天）
 * @property integer    $money_status               资金状态（0、未支付，1、已入账，2、退款中，3、部分退款，4、已退款）
 * @property integer    $order_status               订单状态（0、等待支付，1、正常订单，2、退款订单，3、已关闭订单，8、锁定状态）
 * @property integer    $pay_type                   支付类型（1、微信支付，2、支付宝支付）
 * @property string     $pay_time                   支付时间
 * @property integer    $order_type                 订单类别（1、系统订单）
 * @property string     $effective_time             软件服务生效时间
 * @property integer    $created
 * @property integer    $modified
 * @property integer    $code_img_url               二维码图片
 * @property string     $address                    地址信息JSON数据
 */
class ShopSalesOrder extends BaseModel
{
    // 资金状态
    const DID_NOT_PAY           = 0;  // 未支付
    const ENTERED_ACCOUNT       = 1;  // 已入账
    const REFUND_ING            = 2;  // 退款中
    const PART_OF_THE_REFUND    = 4;  // 部分退款
    const REFUNDED              = 3;  // 已退款
    public static $MoneyStatus = [
        self::DID_NOT_PAY           => "未支付",
        self::ENTERED_ACCOUNT       => "支付成功",
        self::REFUND_ING            => "退款中",
        self::PART_OF_THE_REFUND    => "部分退款",
        self::REFUNDED              => "已退款"
    ];

    // 订单状态
    const NORMAL_ORDER  = 1;
    const REFUND_ORDER  = 2;
    const CLOSE_ORDER   = 3;
    const LOCK_ORDER    = 4;
    public static $OrderStatus = [
        self::NORMAL_ORDER  => "正常订单",
        self::REFUND_ORDER  => "退款订单",
        self::CLOSE_ORDER   => "关闭订单",
        self::LOCK_ORDER    => "锁定订单"
    ];

    // 支付类型
    const WX = 1;
    const ZFB = 2;
    const OFFLINE = 3;
    public static $PayType = [
        self::WX   => "微信支付",
        self::ZFB    => "支付宝支付",
        self::OFFLINE => '线下支付'
    ];

    // 订单类别
    const SYSTEM_ORDER = 1;
    const OFFLINE_ORDER = 2;
    public static $OrderType = [
        self::SYSTEM_ORDER => "系统订单",
        self::OFFLINE_ORDER => "线下订单"
    ];

    // 支付状态
    const PAY_STATUS_CREATE    = 1;
    const PAY_STATUS_SUCCEED   = 2;
    const PAY_STATUS_FAIL      = 3;
    public static  $PayStatus = [
        self::PAY_STATUS_CREATE    => "未支付",
        self::PAY_STATUS_SUCCEED   => "已支付",
        self::PAY_STATUS_FAIL      => "支付失败",
    ];

    const SHOP_NAME     = 1;    // shop_name
    const SHOP_ID       = 2;    // shop_id
    const AGENT_NAME    = 1;    // agent_name
    const AGENT_ID      = 2;    // agent_id

    // 默认字段
    public static $allField = ['shop_id', 'order_no', 'transaction_id', 'out_transaction_id',
        'shop_product_version_id', 'shop_product_hardware_info', 'money_status',
        'order_status','pay_type','code_img_url', 'pay_time', "software_service_spec",
        'setup_fee', 'software_service_fee', 'hardware_purchase_cost', 'total_order_amount', 'actual_amount', "effective_time", "order_type",
        "rule_id", "commission", "pay_status", "address", 'mobile', 'app_id', 'agent_id', 'customer_id', 'promotion_id', 'pay_type'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_sales_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'app_id', 'agent_id', 'customer_id', 'promotion_id', 'pay_type'], 'required', 'on' => 'edit'],  // 下单手机、平台id、服务商id、商家id、地推员id
            [[
                'shop_id', 'order_no', 'transaction_id', 'out_transaction_id',
                'shop_product_version_id', 'shop_product_hardware_info', 'money_status',
                'order_status','pay_type','code_img_url', 'pay_time', "software_service_spec",
                'setup_fee', 'software_service_fee', 'hardware_purchase_cost', 'total_order_amount', 'actual_amount', "effective_time", "order_type",
                "rule_id", "commission", "pay_status", "address"
            ], 'safe'],
        ];
    }

    /**
     * 设置场景
     * @return array|void
     */
    public function scenarios()
    {
        return [
            'default' => self::$allField,
            'edit' => self::$allField
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                    'pay_time' => SORT_DESC,
                ]
            ],
        ]);

        if (!($this->load([$this->formName()=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        $query = $this->_addWhere($query, $params);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        if (!empty($params['getData'])) {
            return $query->asArray()->all();
        }

        return $dataProvider;
    }

    /**
     * 支付详情
     */
    public function getPayInfo()
    {
        return $this->hasMany(ShopSalesOrderPayInfoModel::className(),['sales_order_id'=>'id']);
    }

    /**
     * 商户信息
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(ShopBase::className(),['shop_id'=>'shop_id'])->select([
            "shop_id", "name shop_name"
        ]);
    }


    /**
     * 商户信息
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(),['id'=>'customer_id'])->select([
            "id", "name", "review_status", "mobile", "account_type","open_order_id","finance_audit","open_pay_status"
        ])->with(["shopBase"]);
    }

    /**
     * 服务商信息
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(AgentBase::className(),['agent_id'=>'agent_id'])->select([
            "agent_id", "agent_name"
        ]);
    }

    /**
     * 服务商信息
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(BaseApp::className(),['app_id'=>'app_id'])->select([
            "app_id", "app_name"
        ]);
    }

    /**
     * 版本信息
     * @return \yii\db\ActiveQuery
     */
    public function getVersion()
    {
        return $this->hasOne(ShopProductVersion::className(),['id'=>'shop_product_version_id'])->select([
            "id", "name"
        ]);
    }

    /**
     * 地推员
     * @return \yii\db\ActiveQuery
     */
    public function getPromotion()
    {
        return $this->hasOne(AgentPromotionAccount::className(),['id'=>'promotion_id'])->select([
            "id", "true_name"
        ]);
    }

    /**
     * 获取基础总数据
     * @param $params
     * @return array
     */
    public function Total($params) {
        $query = self::find()->select([
            "SUM(".self::tableName().".actual_amount) total_amount",
            "count(distinct(".self::tableName().".customer_id)) total_pay_shop",
            "count(".self::tableName().".id) total_pay_num"
        ])->where([
            self::tableName().".pay_status" => ShopSalesOrderPayInfoModel::SUCCEED
        ]);

        $this->load([$this->formName()=>$params]);
        $query = $this->_addWhere($query, $params);
        $total = self::findBySql($query->createCommand()->getRawSql())->limit(1)->asArray()->one();

        $retData['total_amount']            = !empty($total['total_amount'])        ? (int)$total['total_amount'] : 0;          // 总支付金额（分）
        $retData['total_pay_shop']          = !empty($total['total_pay_shop'])      ? (int)$total['total_pay_shop'] : 0;        // 累计实付商户数
        $retData['total_pay_num']           = !empty($total['total_pay_num'])       ? (int)$total['total_pay_num'] : 0;         // 累计实付笔数
        return $retData;
    }


    /**
     * 格式化数据
     * @param $params
     * @return array
     * */
    public static function _formatting($params){
        // 商户信息
        if (!empty($params['shop_type'])) {
            if ($params['shop_type'] == self::SHOP_NAME) {
                $params['shop_name'] = $params['shop'];
            } else {
                $params['shop_id'] = $params['shop'];
            }
            unset($params['shop_type']);
            unset($params['shop']);
        }

        // 商户信息
        if (!empty($params['agent_type'])) {
            if ($params['agent_type'] == self::AGENT_NAME) {
                $params['agent_name'] = $params['agent'];
            } else {
                $params['agent_id'] = $params['agent'];
            }
            unset($params['agent_type']);
            unset($params['agent']);
        }

        return $params;
    }

    /**
     * 格式化数据
     * @param $customer_id
     * @return string
     * */
    public static function _getShopId($customer_id){
        return ShopBase::find()->select([
            "shop_id"
        ])->where([
            "customer_id" =>$customer_id
        ])->scalar();
    }

    /**
     * 添加 where 条件
     * @param $query
     * @param $params
     * @return
     * */
    public function _addWhere($query, $params){
        // 商户信息查询   （由于开户成功后  未得到商户信息的同步 所以只能根据 customer 信息去关联）
        if (!empty($params['shop_name']) || !empty($params['shop_id']) || !empty($params['short_name'])) {
            $query->leftJoin(Customer::tableName(), Customer::tableName().".id = ".self::tableName().".customer_id");
            $query->leftJoin(ShopBase::tableName(), ShopBase::tableName().".customer_id = ".Customer::tableName().".id");

            if (!empty($params['shop_name'])) {
                $query->andFilterWhere(["like", Customer::tableName().".name", $params['shop_name']]);
                $query->OrFilterWhere(["like", ShopBase::tableName().".name", $params['shop_name']]);
            }

            if (!empty($params['shop_id'])) {
                $query->OrFilterWhere([ShopBase::tableName().".shop_id" => $params['shop_id']]);
            }

            if (!empty($params['short_name'])) {
                $query->andFilterWhere(["like", Customer::tableName().".short_name", $params['short_name']]);
            }
        }

        $query->andFilterWhere([
            self::tableName().'.id' => $this->id,
            self::tableName().'.mobile' => $this->mobile,
            self::tableName().'.app_id' => $this->app_id,
            self::tableName().'.agent_id' => $this->agent_id,
            self::tableName().'.customer_id' => $this->customer_id,
            self::tableName().'.promotion_id' => $this->promotion_id,
            //self::tableName().'.shop_id' => $this->shop_id,
            self::tableName().'.order_no' => $this->order_no,
            self::tableName().'.transaction_id' => $this->transaction_id,
            self::tableName().'.out_transaction_id' => $this->out_transaction_id,
            self::tableName().'.shop_product_version_id' => $this->shop_product_version_id,
            self::tableName().'.setup_fee' => $this->setup_fee,
            self::tableName().'.software_service_fee' => $this->software_service_fee,
            self::tableName().'.hardware_purchase_cost' => $this->hardware_purchase_cost,
            self::tableName().'.total_order_amount' => $this->total_order_amount,
            self::tableName().'.actual_amount' => $this->actual_amount,
            self::tableName().'.software_service_spec' => $this->software_service_spec,
            self::tableName().'.shop_product_hardware_info' => $this->shop_product_hardware_info,
            self::tableName().'.money_status' => $this->money_status,
            self::tableName().'.order_status' => $this->order_status,
            self::tableName().'.pay_type' => $this->pay_type,
            self::tableName().'.pay_time' => $this->pay_time,
            self::tableName().'.effective_time' => $this->effective_time,
            self::tableName().'.code_img_url' => $this->code_img_url,
            self::tableName().'.created' => $this->created,
            self::tableName().'.modified' => $this->modified,
            self::tableName().'.order_type' => $this->order_type,
        ]);

        if (!empty($params['agent_name'])) {
            $query->leftJoin(AgentBase::tableName(), AgentBase::tableName().".agent_id = ".self::tableName().".agent_id");
            if (!empty($params['agent_name'])) {
                $query->andFilterWhere(["like", AgentBase::tableName().".agent_name", $params['agent_name']]);
            }
        }

        if(!empty($params['pay_time_s']) && !empty($params['pay_time_e'])) {
            $query->andWhere(['>=', self::tableName().'.pay_time', strtotime($params['pay_time_s'])]);
            $query->andWhere(['<=', self::tableName().'.pay_time', strtotime($params['pay_time_e'])]);
        }

        // 排除未支付订单
        if (!empty($params['del_not_pay'])) {
            $query->andFilterWhere([self::tableName().".pay_status" => self::PAY_STATUS_SUCCEED]);
        }

        return $query;
    }
}