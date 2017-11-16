<?php

/**
 * Created by PhpStorm.
 * User: ShiYaoJia
 * Date: 2017/08/24
 * Time: 16:50
 */

namespace app\models\baseboss;

use app\common\helpers\ConstantHelper;
use app\common\services\AgentSettleService;
use app\common\vendor\pay\Pay;
use app\models\base\SettleGroup;
use app\models\shop\Customer;
use Yii;
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
 * @property integer    $pay_status                 支付状态（1、创建支付，2、支付完成，3、支付失败）
 * @property string     $pay_time                   支付时间
 * @property integer    $order_type                 订单类别（1、系统订单）
 * @property string     $effective_time             软件服务生效时间
 * @property integer    $created
 * @property integer    $modified
 * @property integer    $code_img_url               二维码图片
 * @property integer    $rule_id                    分佣规则id(关联agent_settle_rules主键)
 * @property integer    $commission                 服务商分佣金额[分]
 * @property string     $address                    收货地址
 * @property string     $remark                     备注
 */
class ShopSalesOrderModel extends PublicModel
{
    // 资金状态
    const DID_NOT_PAY           = 0;  // 未支付
    const ENTERED_ACCOUNT       = 1;  // 已入账
    const REFUND_ING            = 2;  // 退款中
    const PART_OF_THE_REFUND    = 4;  // 部分退款
    const REFUNDED              = 3;  // 已退款
    public static $MoneyStatus = [
        self::DID_NOT_PAY           => "未支付",
        self::ENTERED_ACCOUNT       => "已入账",
        self::REFUND_ING            => "退款中",
        self::PART_OF_THE_REFUND    => "部分退款",
        self::REFUNDED              => "已退款"
    ];

    // 已付款的类型
    public static $EnteredType = [
        self::ENTERED_ACCOUNT, self::REFUND_ING, self::PART_OF_THE_REFUND
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
    const WX = Pay::WX;
    const ZFB = Pay::ZFB;
    const OFFLINE = 3;
    public static $PayType = [
        self::WX   => "微信",
        self::ZFB    => "支付宝",
        self::OFFLINE    => "线下"
    ];
    public static $PayCode = [
        self::WX   => "wx",
        self::ZFB    => "ali"
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

    public static $EffectOrderField = [
        "id", "mobile", "promotion_id", "order_no", "agent_id", "customer_id", "order_no",
        "shop_product_version_id","setup_fee","software_service_fee","hardware_purchase_cost","total_order_amount",
        "transaction_id", "actual_amount", "pay_time", "pay_type", "app_id", "money_status", "software_service_spec", "shop_product_hardware_info",
        "pay_type", "address", 'remark', 'order_type', 'pay_account', 'pay_account_name', 'pay_voucher'
    ];

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
            [['app_id', 'agent_id', 'customer_id', 'promotion_id', 'shop_id', 'shop_product_version_id', 'software_service_spec', 'money_status', 'order_status', 'pay_type', 'pay_status', 'pay_time', 'order_type', 'effective_time', 'created', 'modified', 'rule_id', 'commission_rate'], 'integer'],
            [['setup_fee', 'software_service_fee', 'hardware_purchase_cost', 'total_order_amount', 'actual_amount', 'commission'], 'number'],
            [['shop_product_hardware_info', 'address'], 'string'],
            [['mobile', 'pay_account_name'], 'string', 'max' => 20],
            [['order_no', 'transaction_id', 'out_transaction_id', 'pay_account'], 'string', 'max' => 32],
            [['code_img_url'], 'string', 'max' => 128],
            [['remark', 'pay_voucher'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'app_id' => 'App ID',
            'agent_id' => 'Agent ID',
            'customer_id' => 'Customer ID',
            'promotion_id' => 'Promotion ID',
            'shop_id' => 'Shop ID',
            'order_no' => 'Order No',
            'transaction_id' => 'Transaction ID',
            'out_transaction_id' => 'Out Transaction ID',
            'shop_product_version_id' => 'Shop Product Version ID',
            'setup_fee' => 'Setup Fee',
            'software_service_fee' => 'Software Service Fee',
            'hardware_purchase_cost' => 'Hardware Purchase Cost',
            'total_order_amount' => 'Total Order Amount',
            'actual_amount' => 'Actual Amount',
            'software_service_spec' => 'Software Service Spec',
            'shop_product_hardware_info' => 'Shop Product Hardware Info',
            'money_status' => 'Money Status',
            'order_status' => 'Order Status',
            'pay_type' => 'Pay Type',
            'pay_status' => 'Pay Status',
            'pay_time' => 'Pay Time',
            'order_type' => 'Order Type',
            'effective_time' => 'Effective Time',
            'created' => 'Created',
            'modified' => 'Modified',
            'code_img_url' => 'Code Img Url',
            'rule_id' => 'Rule ID',
            'commission_rate' => 'Commission Rate',
            'commission' => 'Commission',
            'address' => 'Address',
            'remark' => 'Remark',
            'pay_account' => 'Pay Account',
            'pay_account_name' => 'Pay Account Name',
            'pay_voucher' => 'Pay Voucher',
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
        ]);

        if (!($this->load(['ShopSalesOrderModel'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'mobile' => $this->mobile,
            'app_id' => $this->app_id,
            'agent_id' => $this->agent_id,
            'customer_id' => $this->customer_id,
            'promotion_id' => $this->promotion_id,
            'shop_id' => $this->shop_id,
            'order_no' => $this->order_no,
            'transaction_id' => $this->transaction_id,
            'out_transaction_id' => $this->out_transaction_id,
            'shop_product_version_id' => $this->shop_product_version_id,
            'setup_fee' => $this->setup_fee,
            'software_service_fee' => $this->software_service_fee,
            'hardware_purchase_cost' => $this->hardware_purchase_cost,
            'total_order_amount' => $this->total_order_amount,
            'actual_amount' => $this->actual_amount,
            'software_service_spec' => $this->software_service_spec,
            'shop_product_hardware_info' => $this->shop_product_hardware_info,
            'money_status' => $this->money_status,
            'order_status' => $this->order_status,
            'pay_type' => $this->pay_type,
            'pay_status' => $this->pay_status,
            'pay_time' => $this->pay_time,
            'effective_time' => $this->effective_time,
            'code_img_url' => $this->code_img_url,
            'created' => $this->created,
            'modified' => $this->modified,
            'order_type' => $this->order_type,
            'rule_id' => $this->rule_id,
            'commission' => $this->commission,
            'address' => $this->address,
            'remark' => $this->remark,
        ]);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if(!empty($params['startDate'])){
            $query->andFilterWhere(['>=','pay_time',$params['startDate']]);
        }
        if(!empty($params['endDate'])){
            $query->andFilterWhere(['<=','pay_time',$params['endDate']]);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        return $dataProvider;
    }

    /**
     * 获取图片
     */
    public function getPayInfo()
    {
        return $this->hasMany(ShopSalesOrderPayInfoModel::className(),['sales_order_id'=>'id']);
    }

    /**
     * 订单支付成功修改
     * @param $params
     * @return bool
     */
    public static function SucceedOrder($params) {
        $Model = self::findOne($params['id']);
        if(empty($Model)) return false;

		if (empty($Model->customer_id) || empty($Model->shop_product_version_id)) {
            return false;
        }
        // 修改Customer 版本信息      若已经有版本 则不修改
        $Customer = Customer::findOne($Model->customer_id);
        if (empty($Customer->version_id) || $Customer->version_id == 0) {
            $Customer->version_id = $Model->shop_product_version_id;
            $Version = ShopProductVersionModel::findOne($Model->shop_product_version_id);
            $Customer->version_type = $Version->version_type;
            if (!$Customer->save()) {
                return false;
            }
        }

        // 修改订单信息
        $Model->money_status = self::ENTERED_ACCOUNT;
        $Model->pay_status = ShopSalesOrderPayInfoModel::SUCCEED;
        $Model->pay_time = strtotime($params['time_end']);
        $Model->transaction_id = $params['transaction_id'];
        if (!$Model->save()) {
            return false;
        }
        return true;
    }

    /**
     * 是否拥有支付完成订单
     * @param $params
     * @return bool
     * */
    public static function _isHavePayOrder($params){
        $zeroOrder = self::_isZero($params);
        if ($zeroOrder) {
            return true;
        }
        $query = Customer::find()->where([
            "id" => $params['customer_id']
        ])->select(["open_order_id"]);
        $data = $query->scalar();
        if (empty($data)) {
            return false;
        }
        $order_type = ShopSalesOrderModel::find()->select("order_type")->where(["id" => $data])->scalar();

        //  线下订单不算支付完成订单
        if ($order_type == ShopSalesOrderModel::OFFLINE_ORDER) {
            return false;
        }

        return true;
    }

    /**
     * 获取生效订单信息
     * @param $params
     * @return array
     * */
    public static function _getEffectOrder($params){
        $customer = Customer::findOne($params['customer_id']);
        if (empty($customer) || empty($customer->version_id)) {
            return null;
        }
        $zeroOrder = self::_isZero($params);
        if ($zeroOrder) {
            return $zeroOrder;
        }
        $orderList = self::find()->select(self::$EffectOrderField)->where([
            "customer_id" => $params['customer_id'],
            "shop_product_version_id" => $customer->version_id
        ])->asArray()->all();

        if (!$orderList) {
            return null;
        }

        if (count($orderList) == 1) {
            return reset($orderList);
        }
        $Ids = [];
        foreach ($orderList as $v) {
            $Ids[] = $v['id'];
        }
        $shop_sales_order_id = ShopSalesOrderPayInfoModel::find()->where([
            "shop_sales_order_id" => $Ids,
            "pay_status" => ShopSalesOrderPayInfoModel::SUCCEED
        ])->orderBy("created")->select("shop_sales_order_id")->limit(1)->scalar();

        $order = ShopSalesOrderModel::find()->select(self::$EffectOrderField)->where([
            "id" => $shop_sales_order_id
        ])->limit(1)->asArray()->one();

        return $order;
    }


    public static function _isZero($params){
        $customer = Customer::findOne($params['customer_id']);
        if (empty($customer) || empty($customer->version_id)) {
            return null;
        }
        $order = self::find()->select([])->where([
            "customer_id" => $params['customer_id'],
            "shop_product_version_id" => $customer->version_id,
            "money_status" => self::$EnteredType,
            "actual_amount" => 0
        ])->limit(1)->asArray()->one();
        if (!empty($order)) {
            return $order;
        }
        return false;
    }

    /**
     * 格式化数据
     * @param $order
     * @return array
     * */
    public static function _formatting($order){
        // 硬件详情添加name img
        $order['shop_product_hardware_info'] = json_decode($order['shop_product_hardware_info'], true);
        if (!empty($order['shop_product_hardware_info'])) {
            foreach ($order['shop_product_hardware_info'] as $k => $v) {
                $product = ShopProductModel::find()->select([
                    "name", "img"
                ])->where([
                    "id" => $k
                ])->limit(1)->asArray()->one();
                $order['shop_product_hardware_info'][$k]['name'] = $product['name'];
                $order['shop_product_hardware_info'][$k]['img'] = $product['img'];
            }
        } else {
            $order['shop_product_hardware_info'] = [];
        }

        // 金额转整形
        $order['setup_fee'] = !empty($order['setup_fee']) ? (int)$order['setup_fee'] : 0;
        $order['software_service_fee'] = !empty($order['software_service_fee']) ? (int)$order['software_service_fee'] : 0;
        $order['hardware_purchase_cost'] = !empty($order['hardware_purchase_cost']) ? (int)$order['hardware_purchase_cost'] : 0;
        $order['total_order_amount'] = !empty($order['total_order_amount']) ? (int)$order['total_order_amount'] : 0;
        $order['actual_amount'] = !empty($order['actual_amount']) ? (int)$order['actual_amount'] : 0;

        if (!empty($order['shop_product_version_name'])) {
            // 添加版本名称
            $order['shop_product_version_name'] = ShopProductVersionModel::find()->select(["name"])->where(["id" => $order['shop_product_version_id']])->scalar();
        } else {
            $order['shop_product_version_name'] = "";
        }

        // 收货地址
        if (!empty($order['address'])) {
            $order['address'] = json_decode($order['address'], true);
        }
        return $order;
    }

    /**
     * 返回默认订单信息
     * @return array
     * */
    public static function retDefaultOrder(){
        return $order = [
            "setup_fee" => 0,
            "software_service_fee" => 0,
            "hardware_purchase_cost" => 0,
            "total_order_amount" => 0,
            "actual_amount" => 0,
            "software_service_spec" => intval((strtotime("2017-12-31 59:59:59") - time())/84600),
            "shop_product_hardware_info" => [],
            "address" => "",
            "remark" => "",
            "shop_product_version_name" => "免费版"
        ];
    }
}