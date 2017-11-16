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
 * @property integer    $refund_no          退款单号（唯一索引）
 * @property integer    $order_no           订单号
 * @property integer    $trans_no           第三方交易号
 * @property integer    $trans_refund_no    第三方退款号
 * @property integer    $app_id             平台id
 * @property integer    $agent_id           服务商id
 * @property integer    $customer_id        商家id
 * @property integer    $shop_id            商户id
 * @property integer    $order_type         订单类别（1、开户订单，2、续费订单）
 * @property integer    $refund_amount      退款金额
 * @property integer    $refund_val         退款值
 * @property integer    $refund_status      退款状态（0、申请退款，1、退款中、2、退款失败，3、退款成功）
 * @property integer    $refund_info        退款信息
 * @property integer    $refund_time        退款时间
 * @property integer    $refund_num         退款次数
 * @property integer    $pay_type           支付类型（0、线下支付，1、微信支付，2、支付宝支付）
 * @property integer    $remark             备注
 * @property integer    $created
 * @property integer    $modified
 * @property integer    $audit_status       审核状态（0、待审核，1、审核通过，2、审核不通过）
 * @property integer    $audit_people_id    审核人（操作员id）
 * @property integer    $audit_time         审核时间
 * @property integer    $lading_people_id   提单人（操作员id）
 * @property integer    $lading_time        提单时间
 * @property string     $audit_remark       审核备注
 */
class ShopOrderRefund extends BaseModel
{
    const OPEN_ACCOUNT_ORDER = 1; // 开户订单
    const RENEWAL_ORDER      = 2; //续费订单
    public static $OrderType = [
        self::OPEN_ACCOUNT_ORDER => "开户订单",
        self::RENEWAL_ORDER      => "续费订单",
    ];

    const APPLY_REFUND      = 1;    // 申请退款
    const REFUND_ING        = 2;    // 退款中
    const REFUND_FAILURE    = 3;    // 退款失败
    const REFUND_SUCCESS    = 4;    // 退款成功
    public static $RefundStatus = [
        self::APPLY_REFUND      => "申请退款",
        self::REFUND_ING        => "退款中",
        self::REFUND_FAILURE    => "退款失败",
        self::REFUND_SUCCESS    => "退款成功",
    ];

    const OFF_LINE_PAY      = 0;    // 线下支付
    const WX_PAY            = 1;    // 微信支付
    const ALI_PAY           = 2;    // 支付宝支付（阿里支付）
    const SWIFT_PASS_PAY    = 3;    // 威富通支付
    public static $PayType = [
        self::OFF_LINE_PAY      => "线下支付",
        self::WX_PAY            => "微信支付",
        self::ALI_PAY           => "支付宝支付",
        self::SWIFT_PASS_PAY    => "威富通支付",
    ];

    const REFUND_TO_AUDIT           = 1; // 退款待审核
    const REFUND_AUDIT_APPROVAL     = 2; // 退款审核通过
    const REFUND_AUDIT_NOT_APPROVAL = 3; // 退款审核不通过
    public static $AuditStatus = [
        self::REFUND_TO_AUDIT           => "待审核",
        self::REFUND_AUDIT_APPROVAL     => "审核通过",
        self::REFUND_AUDIT_NOT_APPROVAL => "审核不通过",
    ];

    // 默认所有字段
    public static $allField = [
        'id',
        'refund_no', 'order_no', 'trans_no', 'trans_refund_no',
        'app_id', 'agent_id', 'customer_id', 'shop_id',
        'order_type', 'refund_amount', 'refund_val', 'refund_status', 'refund_info',
        'refund_time', 'refund_num', 'pay_type', 'remark',
        'audit_status', 'audit_people_id', 'audit_time', 'lading_people_id', 'lading_time', "audit_remark"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order_refund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'refund_no','order_no','app_id','agent_id', 'customer_id'
                ,'order_type','refund_amount','refund_val','refund_status','refund_num','pay_type'
            ], 'required', 'on' => 'edit'],
            [self::$allField, 'safe']
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
                    'refund_time' => SORT_DESC,
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

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['getData'])) {
            return $query->asArray()->all();
        }

        if (!empty($params['getSql'])) {
            return $query->createCommand()->getRawSql();
        }

        return $dataProvider;
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
            "id", "name", "review_status", "mobile", "account_type"
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
     * 订单信息
     * @return \yii\db\ActiveQuery
     */
    public function getSalesOrder()
    {
        return $this->hasOne(ShopSalesOrder::className(),['order_no'=>'order_no']);
    }

    /**
     * 订单信息
     * @return \yii\db\ActiveQuery
     */
    public function getPayOrder()
    {
        return $this->hasOne(ShopServicePay::className(),['pay_no'=>'order_no']);
    }

    /**
     * 平台信息
     * @return \yii\db\ActiveQuery
     */
    public function getApp()
    {
        return $this->hasOne(BaseApp::className(),['app_id'=>'app_id'])->select([
            "app_id", "app_name"
        ]);
    }

    /**
     * 获取基础总数据
     * $param $params
     * @return array
     */
    public function Total($params) {
        $query = self::find()->select([
            "SUM(".self::tableName().".refund_amount) total_refund_amount",
            "count(distinct(".self::tableName().".customer_id)) total_ret_shop",
            "count(".self::tableName().".id) total_ret_num",
        ])->where([
            self::tableName().".refund_status" => self::REFUND_SUCCESS
        ]);
        $query = $this->_addWhere($query, $params);
        $sql = $query->createCommand()->getRawSql();

        $total = self::findBySql($sql)->limit(1)->asArray()->one();

        $retData['total_amount']    = !empty($total['total_refund_amount']) ? (int)$total['total_refund_amount'] : 0;   // 累计退款金额（分）
        $retData['total_ret_shop']  = !empty($total['total_ret_shop'])      ? (int)$total['total_ret_shop'] : 0;        // 累计退款商户数量
        $retData['total_ret_num']   = !empty($total['total_ret_num'])       ? (int)$total['total_ret_num'] : 0;         // 累计退款笔数

        $retData['RefundToAudit'] = self::find()->select(['count(`id`) PendingApproval'])->where(["audit_status" => self::REFUND_TO_AUDIT])->scalar();
        $retData['RefundAuditApproval'] = self::find()->select(['count(`id`) PendingApproval'])->where(["audit_status" => self::REFUND_AUDIT_APPROVAL])->scalar();
        $retData['RefundAuditNotApproval'] = self::find()->select(['count(`id`) PendingApproval'])->where(["audit_status" => self::REFUND_AUDIT_NOT_APPROVAL])->scalar();
        return $retData;
    }

    /**
     * 可退金额
     * @param $order_no
     * @param $type
     * @param $refund_no
     * @return string
     */
    public static function _canBeRefundAmount($order_no, $type, $refund_no = null){
        if ($type == self::OPEN_ACCOUNT_ORDER) {
            $Model = new ShopSalesOrder();
            $field = "actual_amount";
            $NoField = "order_no";
        } else {
            $Model = new ShopServicePay();
            $field = "paid_amount";
            $NoField = "pay_no";
        }

        $query = self::find()->select([
            "SUM(refund_amount)"
        ])->where([
            "order_no" => $order_no,
            "order_type" => $type
        ]);
        if (!empty($refund_no)) {
            $query->andFilterWhere(["!=", "refund_no", $refund_no]);
        }
        $RetiredAmount = $query->limit(1)->scalar();
        if (!$RetiredAmount) {
            $RetiredAmount = 0;
        }

        $TotalAmount = $Model->find()->select([$field])->where([$NoField=>$order_no])->limit(1)->scalar();

        return ((int)$TotalAmount - (int)$RetiredAmount);
    }


    /**
     * 可扣除天数
     * @param $order_no
     * @param $type
     * @param $shop_id
     * @return string
     * */
    public static function _canBeRefundDate($order_no, $type, $shop_id = null){
        if ($type == ShopOrderRefund::OPEN_ACCOUNT_ORDER) {
            $Model = ShopSalesOrder::find()->select(["software_service_spec"])->where(['order_no' => $order_no])->one();
            $order_total_num = $Model['software_service_spec'];
        } else {
            $Model = ShopServicePay::find()->select(["purchase_count", "gift_count"])->where(['pay_no' => $order_no])->one();
            $order_total_num = $Model['purchase_count'] + $Model['gift_count'];
        }

        $RetiredData = self::find()->select([
            "SUM(refund_val)"
        ])->where([
            "order_no" => $order_no,
            "order_type" => $type
        ])->limit(1)->scalar();

        if (!$RetiredData) {
            $RetiredData = 0;
        }

        $order_total_num -= $RetiredData;

        $ExpireTime = 0;
        if (!empty($shop_id)) {
            $ExpireTime = Customer::_getExpireTime($shop_id);
        }

        $SurplusTime = floor(($ExpireTime - time()/86400));

        if ($SurplusTime > $order_total_num) {
            $SurplusTime = $order_total_num;
        }

        if ($SurplusTime < 0) {
            $SurplusTime = 0;
        }

        return $SurplusTime;
    }

    /**
     * 获取 关联的订单详情 url
     * @param $order_no
     * @param $order_type
     * @return string
     * */
    public static function _getOrderUrl($order_no, $order_type){
        if ($order_type == self::OPEN_ACCOUNT_ORDER) {
            $order_id = ShopSalesOrder::find()->where(["order_no" => $order_no])->select(["id"])->scalar();
            $url = "/admin/shop-sales-order/detail?id=".$order_id;
        } else {
            $order_id = ShopServicePay::find()->where(["pay_no" => $order_no])->select(["id"])->scalar();
            $url = "/admin/shop-service/pay-detail?id=".$order_id;
        }

        return $url;
    }


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
            self::tableName().'.refund_no' => $this->refund_no,
            self::tableName().'.order_no' => $this->order_no,
            self::tableName().'.trans_no' => $this->trans_no,
            self::tableName().'.trans_refund_no' => $this->trans_refund_no,
            self::tableName().'.app_id' => $this->app_id,
            self::tableName().'.agent_id' => $this->agent_id,
            self::tableName().'.shop_id' => $this->shop_id,
            self::tableName().'.order_type' => $this->order_type,
            self::tableName().'.refund_amount' => $this->refund_amount,
            self::tableName().'.refund_val' => $this->refund_val,
            self::tableName().'.refund_status' => $this->refund_status,
            self::tableName().'.refund_info' => $this->refund_info,
            self::tableName().'.refund_time' => $this->refund_time,
            self::tableName().'.refund_num' => $this->refund_num,
            self::tableName().'.pay_type' => $this->pay_type,
            self::tableName().'.remark' => $this->remark,
            self::tableName().'.created' => $this->created,
            self::tableName().'.modified' => $this->modified,
            self::tableName().'.audit_status' => $this->audit_status,
            self::tableName().'.audit_people_id' => $this->audit_people_id,
            self::tableName().'.audit_time' => $this->audit_time,
            self::tableName().'.lading_people_id' => $this->lading_people_id,
            self::tableName().'.lading_time' => $this->lading_time,
            self::tableName().".audit_remark" => $this->audit_remark
        ]);

        if (!empty($params['agent_name'])) {
            $query->leftJoin(AgentBase::tableName(), AgentBase::tableName().".agent_id = ".self::tableName().".agent_id");
            $query->andFilterWhere(["like", AgentBase::tableName().".agent_name", $params['agent_name']]);
        }

        if (!empty($params['created_s'])) {
            $query->andFilterWhere([">=", AgentBase::tableName().".created", $params['created_s']]);
        }
        if (!empty($params['created_e'])) {
            $query->andFilterWhere(["<=", AgentBase::tableName().".created", $params['created_e']]);
        }

        return $query;
    }
}