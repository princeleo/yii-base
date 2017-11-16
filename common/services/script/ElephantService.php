<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/19
 * Time: 20:48
 */

namespace app\common\services\script;

use app\common\helpers\BaseHelper;
use app\common\helpers\ConstantHelper;
use app\common\services\AgentSettleService;
use app\models\bank_channel\Rate;
use app\models\base\SettleGroup;
use app\models\shop\CustomerPaymentSetting;
use app\models\shop\ShopBase;
use app\models\shop\ShopOrder;
use app\models\shop\ShopOrderDetail;
use app\models\shop\ShopSubPaymentSettings;
use Yii;
use yii\base\Exception;

/**
 * Class ZhctService
 * @package app\common\services\script
 */
class ElephantService extends BaseService {
    /**
     * 应用id
     * @var string
     */
    private static $appId = ConstantHelper::PLATFORM_ELEPHANT;

    private $agentSettleService;

    private $shopOrderDefault = null;

    private $shopList = null;
    private $shopIds = null;
    private $refundList = array();

    const ORDER_STATUS_1 = 1;//初始下单
    const ORDER_STATUS_2 = 2;//支付中
    const ORDER_STATUS_3 = 3; //已支付
    const ORDER_STATUS_4 = 4;//已接单
    const ORDER_STATUS_5 = 5;//已完成
    const ORDER_STATUS_7 = 7;//已关闭

    const ORDER_TYPE_1 = 1;//普通订单
    const ORDER_TYPE_2 = 2;//扫码订单
    const ORDER_TYPE_3 = 3;//扫码收银订单

    public static function getBossOrderType()
    {
        return [
            self::ORDER_TYPE_1 => ShopOrder::ORDER_TYPE_COMMON,
            self::ORDER_TYPE_2 => ShopOrder::ORDER_TYPE_SCAN_C2B,
            self::ORDER_TYPE_3 => ShopOrder::ORDER_TYPE_SCAN_B2C,
        ];
    }

    /**
     * @var
     */
    private $nowTime;

    const ORDER_URL = '/order/order-flow';

    /**
     * @param $params
     * @return mixed|string
     * @throws \app\common\exceptions\ScriptException
     */
    public function getOrderDaily($params)
    {
        $url = trim(GATEWAY_BOSS, '/').self::ORDER_URL;
        return $this->httpCurlPost($url, $params);
    }

    /**
     * @param $data
     * @return bool
     */
    public function saveDailyOrders($data)
    {
        if (empty($data) || !is_array($data)) {
            return true;
        }
        empty($this->nowTime) && $this->nowTime = time();
        empty($this->agentSettleService) && $this->agentSettleService = new AgentSettleService();

        $shopOrderDetailFields = null;
        $shopOrderDetailSql = null;
        $shopOrderDetailFields = 'boss_order_no,app_id,order_no,product_info,discount_info,consignee_info,delivery_info,order_log_info,created,modified';

        $orderVals = '';
        $orderDetailVals = '';

        $this->shopList = null;
        $this->shopIds = null;
        foreach($data as $item) {
            $this->shopIds[$item['order']['merc_id']] = $item['order']['merc_id'];
        }
        $tmp = ShopBase::find()->select('merchant_id,shop_id,agent_id')->andWhere('shop_id in ('.implode(',',array_keys($this->shopIds)).')')->asArray()->all();
        foreach ($tmp as $row) {
            $this->shopList[$row['shop_id']] = $row;
        }
        foreach($data as $item) {
            $shopOrder = $this->formatShopOrder($item['order'],$item['payInfo'],$item['preferential']); //订单信息

            if ($shopOrder) {
                $valueStr = '';
                foreach ($shopOrder as $k => $v) {
                    $valueStr .= '\''.$v.'\',';
                }
                $orderVals .= '('.trim($valueStr,',').'),';

                $productInfo = $consigneeInfo = $deliveryInfo = $orderLogInfo = $discountInfo = '';
                if(!empty($item['detail'])){
                    $productInfo = json_encode($this->formatOrderDetailInfo('product', $item['detail']), JSON_UNESCAPED_UNICODE); //订单商品
                }
                if(!empty($item['discount_info'])){
                    $discountInfo = json_encode($this->formatOrderDetailInfo('discount', $item['discount_info']), JSON_UNESCAPED_UNICODE); //折扣信息
                }
                if(!empty($item['consignee_info'])){
                    $consigneeInfo = json_encode($this->formatOrderDetailInfo('consignee', $item['consignee_info']), JSON_UNESCAPED_UNICODE); //收货信息
                }
                if(!empty($item['delivery_info'])){
                    $deliveryInfo = json_encode($this->formatOrderDetailInfo('delivery', $item['delivery_info']), JSON_UNESCAPED_UNICODE); //发货信息
                }
                if(!empty($item['order_log_info'])){
                    $orderLogInfo = json_encode($this->formatOrderDetailInfo('order_log', $item['order_log_info']), JSON_UNESCAPED_UNICODE); //订单日志信息
                }

                $orderDetailVals .= "('{$shopOrder['boss_order_no']}','{$shopOrder['app_id']}','{$shopOrder['order_no']}','{$productInfo}','{$discountInfo}','{$consigneeInfo}','{$deliveryInfo}','{$orderLogInfo}','{$this->nowTime}','{$this->nowTime}'),";
                unset($productInfo, $discountInfo, $consigneeInfo, $deliveryInfo, $orderLogInfo);
            }
        }

        $orderVals = trim($orderVals, ',');
        $orderDetailVals = trim($orderDetailVals, ',');

        if ($orderVals && $orderDetailVals) {
            $shopOrderSql = 'REPLACE INTO '.ShopOrder::tableName().' ('.implode(array_keys(self::$shopOrderFieldsMap),',').') values  ';

            $shopOrderDetailSql = 'REPLACE INTO '.ShopOrderDetail::tableName().' ('.$shopOrderDetailFields.') values  ';
            $shopOrderSql .= $orderVals.';';
            $shopOrderDetailSql .= $orderDetailVals.';';

            $transaction = ShopOrder::getDb()->beginTransaction();
            try{
                Yii::$app->db->createCommand($shopOrderSql)->query();
                Yii::$app->db->createCommand($shopOrderDetailSql)->query();

                Yii::info('shopOrder SQL exec | success | '.$shopOrderSql, __METHOD__);
                Yii::info('shopOrderDetail SQL exec | success | '.$shopOrderDetailSql, __METHOD__);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::info('shopOrder SQL exec | fail | '.$shopOrderSql, __METHOD__);
                Yii::info('shopOrderDetail SQL exec | fail | '.$shopOrderDetailSql, __METHOD__);
                Yii::info('saveDailyOrders SQL exec | exception | '.$e->getMessage(), __METHOD__);
            }
            unset($data, $orderVals, $orderDetailVals, $shopOrderSql, $shopOrderDetailSql,$transaction);
        }
        //退款数据不为空,则开始计算支付净额
        if(!empty($this->refundList))
        {
            foreach($this->refundList as $refund)
            {
                $orderItem = ShopOrder::find()->andFilterWhere([
                    'app_id' => $refund['app_id'],
                    'order_no' => $refund['order_no']
                ])->asArray()->one();

                if(!empty($orderItem['paid_amount']) && ($orderItem['paid_amount'] - abs($refund['paid_amount']) >= 0))
                {
                    $select_sql = 'select paid_amount from '.ShopOrder::tableName().' where app_id=\''.$refund['app_id'].'\' and order_no=\''.$refund['order_no'].'\' limit 1;';
                    Yii::$app->db->createCommand($select_sql)->query();

                    $transaction = ShopOrder::getDb()->beginTransaction();
                    $sql = 'update '.ShopOrder::tableName().' set refund_amount='.abs($refund['paid_amount']).' , net_amount = paid_amount - '.abs($refund['paid_amount']).' where app_id=\''.$refund['app_id'].'\' and order_no=\''.$refund['order_no'].'\' limit 1;';
                    try{
                        Yii::$app->db->createCommand($sql)->query();
                        $transaction->commit();
                        Yii::info('shopOrder SQL exec | update refund order | success | '.$sql, __METHOD__);
                    } catch (Exception $e) {
                        $transaction->rollBack();
                        Yii::info('shopOrder SQL exec | update refund order | failed | '.$sql, __METHOD__);
                    }
                }
                else
                {
                    Yii::info('shopOrder SQL exec | paid_amount and  refund_amount price no than  ', __METHOD__);
                }
            }
        }
        return true;
    }

    /**
     * @param $data
     * @return array
     */
    private function formatShopOrder($data,$payinfo,$preferential=null)
    {
        $ret = [];
        if (is_null($this->shopOrderDefault)) {
            $this->shopOrderDefault = (new ShopOrder())->loadDefaultValues()->getAttributes();
        }

        $data = array_merge($this->shopOrderDefault, $data);
        foreach (self::$shopOrderFieldsMap as $k => $v) {
            $ret[$k] = isset($data[$v]) ? $data[$v] : null;
        }
        unset($data);

        if ($ret['order_type'] == ShopOrder::ORDER_TYPE_REFUND) { //退款订单
            //由于订单信息都是批量处理,退款订单需要等待处理完成后再进行处理
            array_push($this->refundList,$ret);
            return [];
        }

        if (!isset($this->shopList[$ret['shop_id']])) {
            Yii::info('saveDailyOrders_getShopInfo | empty | merchant_id['.$ret['shop_id'].']', __METHOD__);
            $ret['agent_id'] = 0;
        } else {
            $ret['agent_id'] = $this->shopList[$ret['shop_id']]['agent_id'];
        }

        $ret['paid_time'] = strtotime($ret['paid_time']);
        $ret['creation_time'] = strtotime($ret['creation_time']);
        if (!empty($ret['rate'])) {
            $ret['rate'] = $ret['rate']/100;
            list($ret['agent_rate'], $ret['commission'], $ret['rule_id']) = $this->agentSettleService->getCommission($ret['agent_id'],$ret['shop_sub_id'],$ret['order_no'],$ret['rate'],$ret['paid_amount'],$ret['paid_time'],SettleGroup::SETTLE_TYPE_TRANS, $ret['app_id']);
        }

        if(is_array($payinfo)){
            foreach($payinfo as $info){
                if(!empty($info['pay_account'])){
                    $payinfo = $info;
                }
            }
        }
        $ret['rule_id'] = !empty($ret['rule_id']) ? $ret['rule_id'] : 0;
        $ret['pay_channel'] = !empty($payinfo['bank_type']) ? $payinfo['bank_type'] : 0;
        $ret['factorage'] = !empty($payinfo['factorage']) ? $payinfo['factorage']/10 : 0; //手续费也不是单位分，导致出错了
        $ret['rate'] = !empty($payinfo['rate']) ? $payinfo['rate']/100 : 0; //由于大象订单存的是千分制
        $ret['account'] = !empty($payinfo['pay_account']) ? $payinfo['pay_account'] : '';
        $ret['pay_status'] = $ret['order_status'] >= self::ORDER_STATUS_3 ? ShopOrder::ORDER_PAY_STATUS_PAID : ShopOrder::ORDER_PAY_STATUS_UNPAID;
        $ret['order_status'] = $ret['order_status'] >= self::ORDER_STATUS_3 ? ShopOrder::ORDER_STATUS_DONE : ShopOrder::ORDER_STATUS_EXCEPTION;
        $ret['order_type'] = self::getBossOrderType()[$ret['order_type']];
        $ret['created'] = $this->nowTime;
        $ret['modified'] = $this->nowTime;

        $ret['boss_order_no'] = BaseHelper::setOrderId(array_search($ret['app_id'], ConstantHelper::$appMap), $ret['order_type']);
        //与银行签订费率
        $ret['sctek_rate'] = 0;
        if (! empty($ret['account'])) {
            $rateId = CustomerPaymentSetting::find()->select(['payment_rate_id'])->where(['account' => $ret['account']])->scalar();
            if ($rateId) {
                $ret['sctek_rate'] = Rate::find()->select('rate')->where(['id' => $rateId])->scalar() ?: 0;
            }
        }
        $ret['sctek_cost'] = number_format($ret['paid_amount']*$ret['sctek_rate']/100, 6, '.',''); //银行手续费 = 支付金额 * 与银行签订费率
        $ret['sctek_income'] = $ret['factorage'] - $ret['sctek_cost']; //盛灿应收款[分] = 手续费 - 银行手续费
        $ret['sctek_profit'] = $ret['factorage'] - $ret['sctek_cost'] - $ret['commission']; //盛灿净利[分] = 手续费 - 银行手续费 - 服务商分佣金额
        $ret['agent_cost'] = number_format($ret['paid_amount']*$ret['agent_rate']/100, 6, '.',''); //银行扣除金额 = 支付金额 * 服务商费率

        $ret['sctek_income'] = $ret['sctek_income'] < 0 ? 0 : $ret['sctek_income'];
        $ret['sctek_profit'] = $ret['sctek_profit'] < 0 ? 0 : $ret['sctek_profit'];

        if(!empty($preferential)){
            $ret['subsidy_total_amount'] = $preferential[0]['total_price'];//第一期 补贴总金额 = 平台补贴金额
            $ret['platform_subsidy_amount'] = $preferential[0]['platform_price'];
            $ret['shop_subsidy_amount'] = 0;//第一期为0
            $ret['gateway_activity_id'] = $preferential[0]['activity_id'];
        }

        return $ret;
    }

    /**
     * @param $key
     * @param $data
     * @return array
     */
    private function formatOrderDetailInfo($key, $data)
    {
        if (empty($data) || !is_array($data) || !isset(self::$shopOrderDetailFieldsMap[$key])) {
            return [];
        }
        return $this->formatOrderDetailInfoLoop($data, self::$shopOrderDetailFieldsMap[$key]);
    }

    /**
     * 格式化数据
     * @param $data
     * @param $map
     * @return array
     */
    private function formatOrderDetailInfoLoop($data, $map)
    {
        if (empty($map['fields'])) {
            return [];
        }
        $ret = [];
        if (!empty($map['type']) && $map['type'] == 'list') { //&& !empty($map['map'])
            foreach ($data as $key => $item) {
                foreach ($map['fields'] as $k => $v) {
                    if (is_array($v)) {
                        if (empty($v['map']) || empty($item[$v['map']])) {
                            $ret[$key][$k] = [];
                        } else {
                            $ret[$key][$k] = $this->formatOrderDetailInfoLoop($item[$v['map']], $v);
                        }
                    } else {
                        $ret[$key][$k] = isset($item[$v]) ? $item[$v] : null;
                    }
                }
            }
        } else {
            foreach ($map as $k => $v) {
                foreach ($map['fields'] as $k => $v) {
                    if (is_array($v)) {
                        if (empty($v['map']) || empty($data[$v['map']])) {
                            $ret[$k] = [];
                        } else {
                            $ret[$k] = $this->formatOrderDetailInfoLoop($data[$v['map']], $v);
                        }
                    } else {
                        $ret[$k] = isset($item[$v]) ? $item[$v] : null;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 订单流水字段映射
     * @var array
     */
    private static $shopOrderFieldsMap = [
        'app_id' 		=>  'app_id',         //下单平台
        'agent_id' 		=>  'agent_id',         //服务商id
        'order_id' 		=>  'id',         //订单id
        'order_no' 		=>  'order_no',         //订单号
        'order_type' 	=>  'order_type',       //订单类型
        'uid' 			=>  'uid',              //下单用户id
        'total_amount' 	=>  'total_price',     //订单总金额[分
        'discount_amount' 	=>  'discount_amount',//订单总金额[分
        'delivery_fee' 	=>  'delivery_fee',     //折扣金额[分]
        'should_pay' 	=>  'payed',       //应付金额[分]
        'creation_time' =>  'created',    //订单创建时间
        'paid_amount' 	=>  'payed',      //支付金额
        'net_amount'    =>  'payed',      //支付净额
        'paid_time' 	=>  'pay_time',        //支付时间
        'order_status' 	=>  'process_status',     //订单状态
        'pay_status' 	=>  'process_status',       //支付状态
        'trans_id' 	    =>  'trans_id',         //支付流水
        'rate'      	=>  'rate',             //手续费率
        'factorage' 	=>  'factorage',        //手续费
        'pay_type' 	    =>  'pay_type',         //支付方式
        'pay_channel' 	=>  'channel',          //channel:支付渠道1:中信 2:浦发
        'nickname' 		=>  'user_name',         //下单用户昵称
        'shop_id' 		=>  'merc_id',          //商户id
        'shop_sub_id' 	=>  'shop_id',          //分店id
        'shop_sub_name' =>  'shop_sub_name',        //分店名称
        'created' 	    =>  'created',          //创建时间
        'modified' 	    =>  'modified',         //最后修改时间，
        'commission' 	=>  'commission',       //分佣金额
        'boss_order_no' 	=>  'boss_order_no',       //Boss平台订单号

        'settlement' 	=>  'settlement',       //结算方式 1.独立门店结算、2.总部结算
        'account' 	    =>  'merchantId',          //收款商户号
        'business_type' 	=>  'business_type',       //业务类型 1：线上 2：线下
        'sctek_rate' 	=>  'sctek_rate',       //与银行所签费率
        'sctek_cost' 	=>  'sctek_cost',       //银行扣除手续费
        'rule_id' 	    =>  'rule_id',          //分佣规则id
        'sctek_income' 	=>  'sctek_income',       //公司收入
        'sctek_profit' 	=>  'sctek_profit',       //公司净利
        'agent_rate' 	=>  'agent_rate',       //服务商费率
        'agent_cost' 	=>  'agent_cost',       //服务商成本

        'subsidy_total_amount' 	=>  'subsidy_total_amount',       //补贴总金额
        'platform_subsidy_amount' 	=>  'platform_subsidy_amount',       //平台补贴金额
        'shop_subsidy_amount' 	=>  'shop_subsidy_amount',       //商户补贴金额
        'gateway_activity_id' 	=>  'gateway_activity_id',       //活动ID(接口)


    ];

    /**
     * 订单详情字段映射
     * @var array
     */
    private static $shopOrderDetailFieldsMap = [
        'product' => [
            'type' => 'list',
            'fields' => [
                'id'=>'product_id',             //商品id		[Integer]
                'name'=>'product_name',         //商品名称		[String]
                'sku_id'=>'product_sku_id',     //商品skuid		[Integer]
                'sku_name'=>'sku_name', //商品sku名称	[String]
                'img'=>'img',           //商品图片地址	[String]
                'price'=>'cost_price',       //价格(分)		[Integer]
                'count'=>'num'        //购买数量		[Integer]
            ]
        ],
        'discount' => [
            'type' => 'list',
            'fields' => [
                'name'=>'name',         //优惠名称		[String]
                'amount'=>'amount',     //优惠金额(分)  [Integer]
            ]
        ],
        'consignee' => [
            'fields' => [
                'id'=>'id',             //收货信息id	[Integer]
                'name'=>'name',         //收货人姓名	[String]
                'addr'=>'addr',         //收货人地址	[String]
                'province'=>'province', //所在省		[String]
                'city'=>'city',         //所在市		[String]
                'district'=>'district', //所在区		[String]
                'zipcode'=>'zipcode',   //邮政编码		[String]
                'tel'=>'tel',           //联系电话      [Integer]
                'remark'=>'remark',     //用户备注      [Integer]
            ]
        ],
        'delivery' => [
            'fields' => [
                'staff_id'=>'staff_id',         //发货人id		[String]
                'staff_name'=>'staff_name',     //发货人姓名	[String]
                'express_no'=>'express_no',     //快递单号		[String]
                'express_info'=>[               //快递单号信息	[String]
                    'map' => 'express_info',
                    'fields' => [
                        'no' => 'no',               //快递单号		[String]
                        'com' => 'com',             //快递公司		[String]
                        'data' => 'data',           //物流信息		[String]
                    ]
                ],
                'remark' => 'remark',           //发货人备注
                'tel' => 'tel',                 //发货人联系方式
                'created' => 'created',         //发货时间
            ]
        ],
        'refund' => [],
        'order_log' => [],
    ];
}
