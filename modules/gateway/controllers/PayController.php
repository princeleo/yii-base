<?php

namespace app\modules\gateway\controllers;

use app\common\errors\BaseError;
use app\common\helpers\BaseHelper;
use app\common\log\Log;
use app\common\vendor\pay\Pay;
use app\models\baseboss\ShopProductModel;
use app\models\baseboss\ShopProductVersionModel;
use app\models\baseboss\ShopSalesOrderModel;
use app\models\baseboss\ShopSalesOrderPayInfoModel;
use app\models\shop\Customer;
use app\models\shop\CustomerAudit;
use yii\db\Exception;
use Yii;


class PayController extends BaseController{
    public $layout  = false;
    public $DefaultModel;
    public $CheckMeg;

    public function init(){
        parent::init();
        $this->DefaultModel = new ShopSalesOrderModel();
    }

    /**
     * 下单 API
     */
    public function actionPlaceOrder()
    {
        $rules = [
            [[
                "shop_product_version_id", "setup_fee", "software_service_fee", "hardware_purchase_cost",
                "total_order_amount", "actual_amount", "software_service_spec", 'mobile', 'app_id', 'agent_id', 'customer_id', 'promotion_id', "address"
            ], "required"],
            [[
                'shop_id', 'order_no', 'transaction_id', 'out_transaction_id',
                'shop_product_version_id', 'shop_product_hardware_info', 'money_status',
                'order_status','pay_type','code_img_url', 'pay_time', "software_service_spec",
                'setup_fee', 'software_service_fee', 'hardware_purchase_cost', 'total_order_amount', 'actual_amount', "effective_time", "order_type",
                "rule_id", "commission", "address", "remark", "pay_account", "pay_account_name", "pay_voucher", "app"
            ], 'safe'],
        ];
        $params = (array)($this->checkForm($this->paramsPost, $rules));

        if (!empty($params['app'])) {
            $params['app_id'] = $params['app'];
        }

        Log::trace("PayOrder:1", $params);
        // 校验基本信息  手机号、商家id、地推员  是否匹配
        $customer = Customer::find()->select(["id", "mobile", "promotion_id"])
            ->where(["id" => $params['customer_id'], "mobile" => $params['mobile'], "promotion_id" => $params['promotion_id']])->limit(1)->asArray()->one();
        Log::trace("PayOrder:2", $customer);
        if (empty($customer)) { // 校验失败
            Log::warning("OrderMatchesError", $params, __METHOD__);
            return $this->error(BaseError::USER_NOT_EXISTS, "手机号与商户信息匹配错误！");
        }
        unset($customer);

        // 金额校验
        if (!$this->_checkAmount($params)) {
            Log::error("PayCheckError:[mobile.".$params['mobile']."]", $params);
            return $this->error(BaseError::ORDER_AMOUNT_ERROR, $this->CheckMeg);
        }
        Log::trace("PayOrder:3", "OK");
        // 查询商家是否存在已付款订单
//        if ($params['order_type'] != ShopSalesOrderModel::OFFLINE_ORDER && ShopSalesOrderModel::_isHavePayOrder($params)) {
//            $order = ShopSalesOrderModel::_getEffectOrder($params);
//            if (empty($order['open_order_data'])) {
//                $transaction= Yii::$app->db->beginTransaction();//创建事务
//                if (Customer::_setOpenOrder($order)){
//                    $transaction->commit();
//                } else {
//                    $transaction->rollBack();
//                }
//            }
//            $order = ShopSalesOrderModel::_formatting($order);
//            return $this->result($order,BaseError::PAYMENT_HAS_BEEN);
//        }

        // 0 元订单不需要走威富通下单
        if ($params['actual_amount'] == 0 || $params['order_type'] == ShopSalesOrderModel::OFFLINE_ORDER) {
            Log::trace("PayOrder:4", "offline or total is zero");
            if ($params['order_type'] == ShopSalesOrderModel::OFFLINE_ORDER) {
                $params['pay_type'] = ShopSalesOrderModel::OFFLINE;
                $params['money_status'] = ShopSalesOrderModel::DID_NOT_PAY;
            } else {
                $params['pay_type'] = ShopSalesOrderModel::WX;
                $params['money_status'] = ShopSalesOrderModel::ENTERED_ACCOUNT;
                $params['pay_status'] = ShopSalesOrderModel::PAY_STATUS_SUCCEED;
            }
            $order = $this->_createOrder($params);
            if (empty($order)) {
                $this->error(BaseError::SAVE_ERROR);
            }
            if ($order['order_type'] == ShopSalesOrderModel::OFFLINE_ORDER) {
                $transaction= Yii::$app->db->beginTransaction();//创建事务
                if (!Customer::_setOrderId($order['customer_id'], $order['order_no'])) {
                    $transaction->rollBack();
                    $this->error(BaseError::SAVE_ERROR, "绑定订单失败！");
                }
                $transaction->commit();
            }
            return $this->result($order,BaseError::SUCC, "下单成功！等待付款");
        }

        $code_img_url = $order_no = [];
        foreach (ShopSalesOrderModel::$PayCode as $k => $v) {   //  分别生成 支付宝和微信订单
            Log::trace("PayOrder:5", "begin add order");
            // 生成订单
            $params['pay_type'] = $k;
            $order = $this->_createOrder($params);
            Log::trace("PayOrder:6", $order);
            if (empty($order)) {
                $this->error(BaseError::SAVE_ERROR);
            }
            Log::trace("PayOrder:7", "begin get code");
            // 创建支付订单 获取二维码地址
            $payResult = $this->_createCodeImgUrl($order);
            if (!isset($payResult['status']) || $payResult['status'] != 0) {    // 支付参数错误
                $msg = isset($payResult['message']) ? $payResult['message'] : BaseError::getError(BaseError::PAY_PARAMS_ERROR);
                Log::error("PayParamError:[No.".$params['order_no']."]", $payResult);
                return $this->error(BaseError::PAY_PARAMS_ERROR, $msg);
            }
            if (!isset($payResult['result_code']) || $payResult['result_code'] != 0) {  // 创建支付失败
                $msg = isset($payResult['err_msg']) ? $payResult['err_msg'] : BaseError::getError(BaseError::CREATE_PAY_FAIL);
                Log::error("CreatePayFail:[No.".$params['order_no']."]", $payResult);
                return $this->error(BaseError::CREATE_PAY_FAIL, $msg);
            }
            Log::trace("PayOrder:8", "begin save code");
            // 保存 url
            $ret = $this->_upCodeImgUrl($payResult['code_img_url'], $params['order_no']);
            if (!$ret) {
                return $this->error(BaseError::SAVE_ERROR);
            }
            $code_img_url[$v] = $this->DefaultModel->code_img_url;
            $code_img_url[$v."_no"] = $this->DefaultModel->order_no;
        }
        Log::trace("PayOrder:9", "end");
        // 返回图片地址
        return $this->success($code_img_url);
    }

    /**
     *  获取订单信息
     * @param array $id
     * @return array $order
     */
    private function getOrder($id){
        $order = $this->DefaultModel->find()->where([
            "id" => $id
        ])->limit(1)->asArray()->one();
        return $order;
    }

    /**
     *  支付结果
     */
    public function actionResults(){
        $rules = [
            [["customer_id"], "required"],
            [["order_no"], "safe"]
        ];
        $params = (array)($this->checkForm($this->paramsPost, $rules));

        if (!empty($params['order_no'])){
            $PayInfo =  Pay::GetSwiftPassPayInfo($params);
            return $this->success($PayInfo);
        }

        $order = ShopSalesOrderModel::find()
        ->select(ShopSalesOrderModel::$EffectOrderField)
        ->where([
            "money_status" => [ShopSalesOrderModel::ENTERED_ACCOUNT, ShopSalesOrderModel::REFUND_ING, ShopSalesOrderModel::PART_OF_THE_REFUND],
            "customer_id" => $params['customer_id']
        ])->limit(1)->asArray()->one();
        if ($order) {
            $order = ShopSalesOrderModel::_formatting($order);
            return $this->success($order);
        } else {
            $orderList = ShopSalesOrderModel::find()
            ->select(ShopSalesOrderModel::$EffectOrderField)
            ->where([
                "customer_id" => $params['customer_id']
            ])->asArray()->all();
        }

        if (!$orderList) {
            return $this->error(BaseError::NOT_ORDER);
        }

        foreach ($orderList as $k => $v) {
            if ($v['money_status'] == ShopSalesOrderModel::DID_NOT_PAY) {
                if (is_array($v) && !empty($v['order_no'])) {
                    $PayInfo =  Pay::GetSwiftPassPayInfo($v);
                }
                if (!isset($PayInfo['status']) || $PayInfo['status'] != 0) {    // 查询失败
                    Log::error("PayInfoParamFail:[No.".$v['order_no']."]", $PayInfo);
                    continue;
                }
                if (!isset($PayInfo['result_code']) || $PayInfo['result_code'] != 0 || !isset($PayInfo['trade_state'])) {  // 查询结果失败
                    Log::error("PayInfoParamFail(result_code):[No.".$v['order_no']."]", $PayInfo);
                    continue;
                }
                if ($PayInfo['trade_state'] == "SUCCESS" && $this->_createPaySucInfo($PayInfo, $v)) {
                    $v = ShopSalesOrderModel::_formatting($v);
                    return $this->success($v);
                }
            }
        }
        return $this->error(BaseError::USER_NOT_EXISTS, "未收到付款!");
    }

    /**
     *  查询威富通支付结果
     */
    public function actionGetSwiftPassPayInfo(){
        $rules = [
            [["order_no"], "required"]
        ];
        $params = (array)($this->checkForm($this->paramsPost, $rules));

        $order = ShopSalesOrderModel::find()->where([
            "order_no" => $params['order_no']
        ])->asArray()->one();
        $PayInfo =  Pay::GetSwiftPassPayInfo($order);

        if (!isset($PayInfo['status']) || $PayInfo['status'] != 0) {    // 查询失败
            $msg = isset($PayInfo['message']) ? $PayInfo['message'] : BaseError::getError(BaseError::THE_QUERY_FAILS);
            Log::error("PayInfoParamFail:[No.".$params['order_no']."]", $PayInfo);
            return $this->error(BaseError::THE_QUERY_FAILS, $msg);
        }
        if (!isset($PayInfo['result_code']) || $PayInfo['result_code'] != 0 || !isset($PayInfo['trade_state'])) {  // 查询结果失败
            $msg = isset($PayInfo['err_msg']) ? $PayInfo['err_msg'] : BaseError::getError(BaseError::THE_QUERY_FAILS);
            Log::error("PayInfoParamFail(result_code):[No.".$params['order_no']."]", $PayInfo);
            return $this->error(BaseError::THE_QUERY_FAILS, $msg);
        }

        switch($PayInfo['trade_state']){
            case "SUCCESS": // 支付成功
                if (!$this->_createPaySucInfo($PayInfo, $order)){
                    return $this->error(BaseError::SAVE_ERROR);
                }
                return $this->success($PayInfo);
            case "REFUND": // 转入退款
                return $this->error(BaseError::USER_NOT_EXISTS, "改订单已转入退款订单!");
            case "NOTPAY": // 未支付
                return $this->error(BaseError::USER_NOT_EXISTS, "未收到付款!");
            case "CLOSED": // 已关闭
                return $this->error(BaseError::USER_NOT_EXISTS, "订单已关闭!");
            case "PAYERROR": // 支付失败(其他原因，如银行返回失败)
                return $this->error(BaseError::USER_NOT_EXISTS, "支付失败!");
            default:
                return $this->error(BaseError::THE_QUERY_FAILS, "未知的交易状态！（请联系管理员检查接口更新）");
        }
    }


    /**
     * 生成订单
     * @param array $params
     * @return array $order
     * */
    private function _createOrder($params){
        //创建订单
        $params = $this->RemoveEmpty($params);  // 去除空值
        $params['order_no'] = BaseHelper::setOrderId($params['pay_type']); // 生成订单号
        if (!empty($params['address'])) {
            if (!is_array($params['address'])) {
                $params['address'] = json_decode($params['address'], true);
                if (!is_array($params['address'])) {
                    $params['address'] = json_decode($params['address'], true);
                }
            }
            $address['receiver_name'] = !empty($params['address']['receiver_name']) ? $params['address']['receiver_name'] : "";
            $address['receiver_tel'] = !empty($params['address']['receiver_tel']) ? $params['address']['receiver_tel'] : "";
            $address['receiver_address'] = !empty($params['address']['receiver_address']) ? $params['address']['receiver_address'] : "";
            $params['address'] = json_encode($address, true);
        }

        if (!empty($params['shop_product_hardware_info'])) {
            if (!is_array($params['shop_product_hardware_info'])) {
                $params['shop_product_hardware_info'] = json_decode($params['shop_product_hardware_info'], true);
                if (!is_array($params['shop_product_hardware_info'])) {
                    $params['shop_product_hardware_info'] = json_decode($params['shop_product_hardware_info'], true);
                }
            }
            if (is_array($params['shop_product_hardware_info'])) {
                $params['shop_product_hardware_info'] = json_encode($params['shop_product_hardware_info'], true);
            }
        }
        $transaction= Yii::$app->db->beginTransaction();//创建事务
        $this->DefaultModel = new ShopSalesOrderModel();

        try{
            $this->DefaultModel->load([$this->DefaultModel->formName() => $params]);
            if (empty($params['total_order_amount']) || $params['total_order_amount'] == 0 ) {    //  0元订单
                $this->DefaultModel->pay_type = 0;  // 0元订单支付类型
                $this->DefaultModel->pay_status = 2;  // 0元订单支付默认支付完成
                $this->DefaultModel->pay_time = time();  // 0元订单支付时间
            }
            $this->DefaultModel->save();
            $customer = Customer::findOne($params['customer_id']);
            $customer->version_id = $params['shop_product_version_id'];
            if (empty($params['total_order_amount']) || $params['total_order_amount'] == 0 ) {    //  0元订单

                $customer->open_pay_status = Customer::OPEN_PAY_COMPLETE;
                $customer->finance_audit = Customer::FINANCE_AUDIT_SUCCESS;
                $customer->status = Customer::CUSTOMER_AGENT_STATUS_REVIEWING;
                $customer->review_status = Customer::CUSTOMER_BOSS_REVIEWING;
                $customer->open_order_id = $this->DefaultModel->id;

                // 0元订单比较特殊  需要直接财务审核通过
                $customerAudit = new CustomerAudit();
                $audit['customer_id'] = $this->DefaultModel->customer_id;
                $audit['type'] = CustomerAudit::BOSS_AUDIT_STATUS;
                $audit['audit_status'] = CustomerAudit::CUSTOMER_AUDIT_CHECK;
                $audit['status'] =CustomerAudit::AUDIT_STATUS_SUC;
                $audit['title'] = '财务审核结果:0元订单，财务审核系统自动通过';
                $audit['desc'] = "财务审核结果:0元订单，财务审核系统自动通过";
                $audit['action_name'] = "系统审核";
                $customerAudit->auditCreate($audit);

                $audit['customer_id'] = $this->DefaultModel->customer_id;
                $audit['type'] = CustomerAudit::AGENT_AUDIT_STATUS;
                $audit['audit_status'] = CustomerAudit::AGENT_CUSTOMER_REVIEW_OPEN;
                $audit['status'] =CustomerAudit::AUDIT_STATUS_SUC;
                $audit['title'] = '审核通过';
                $audit['desc'] = "0元订单，财务审核系统自动通过";
                $customerAudit->auditCreate($audit);
            }
            if (!empty($params['order_type']) && $params['order_type'] == ShopSalesOrderModel::OFFLINE_ORDER) { // 线下订单
                $customer->open_pay_status = Customer::OPEN_PAY_PENDING_APPROVAL;
                $customer->finance_audit = Customer::FINANCE_AUDIT_DEFAULT;
            }
            $customer->save();

            $transaction->commit();
        }catch(Exception $e){
            $transaction->rollback();
            Log::error("OrderSaveError:[No.".$params['order_no']."]", $e->getMessage(), __METHOD__);
            return null;
        }
        $order = $this->getOrder($this->DefaultModel->id);
        return $order;
    }

    /**
     * 生成二维码图片地址
     * @param array $order
     * @return array $order
     * */
    private function _createCodeImgUrl($order){
        //商品描述
        $order['body'] = (Customer::find()->select(["name"])->where(["id" => $order['customer_id']])->limit(1)->scalar())."开户";
        // 支付结果通知地址
        $REQUEST_SCHEME = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : "http";
        $order['notify_url'] = $REQUEST_SCHEME."://".OPEN_API_HOST."/gate-way/pay-info/swift-pass-pay-info";
        // 订单生成时间 & 失效时间 （300s）失效
        $timeFormat = "YmdHis";
        $order['time_start'] = date($timeFormat, $order['created']);
        $order['time_expire'] = date($timeFormat, ((int)$order['created'] + (int)Pay::TIME_EXPIRE));
        // 生成支付订单  返回支付二维码
        $payResult = Pay::SwiftPassPay($order['pay_type'], $order);
        return $payResult;
    }

    /**
     * 记录二维码图片地址
     * @param array $code_img_url
     * @param string $order_no
     * @return array $order
     * */
    private function _upCodeImgUrl($code_img_url, $order_no){
        $transaction= Yii::$app->db->beginTransaction();//创建事务
        try{
            $this->DefaultModel->code_img_url = $code_img_url;
            $this->DefaultModel->pay_status = ShopSalesOrderPayInfoModel::CREATE;
            $this->DefaultModel->save();
            $PayInfoModel = new ShopSalesOrderPayInfoModel();
            $pay_Info["shop_sales_order_id"] = $this->DefaultModel->id;
            $pay_Info["order_no"] = $this->DefaultModel->order_no;
            $pay_Info["pay_status"] = ShopSalesOrderPayInfoModel::CREATE;
            $PayInfoModel->load([$PayInfoModel->formName() => $pay_Info]);
            $PayInfoModel->save();
            $transaction->commit();
        }catch(Exception $e){
            $transaction->rollback();
            Log::error("OrderSaveError:[No.".$order_no."]", $e->getMessage(), __METHOD__);
            return $this->error(BaseError::SAVE_ERROR);
        }
        return true;
    }


    /**
     * 订单支付成功处理
     * @param $PayInfo
     * @param $order
     * @return array $order
     * */
    private function _createPaySucInfo($PayInfo, $order){
        $data = ShopSalesOrderPayInfoModel::find()->where([
            "order_no" => $order['order_no'],
            "pay_status" => ShopSalesOrderPayInfoModel::SUCCEED
        ])->limit(1)->asArray()->one();
        if (!$data) {
            if (($PayInfo['total_fee'] == $order['actual_amount']) && ($PayInfo['out_trade_no'] == $order['order_no'])) {
                $transaction= Yii::$app->db->beginTransaction();//创建事务
                $PayInfo['order_no'] = $order['order_no'];
                $PayInfo['id'] = $order['id'];
                if (!ShopSalesOrderPayInfoModel::SucceedOrder($PayInfo) || !ShopSalesOrderModel::SucceedOrder($PayInfo)){
                    $transaction->rollBack();
                    Log::error("PayInfoSaveError:[No.".$order['order_no']."]", $PayInfo);
                    return false;
                }
                $transaction->commit();
            }
        }
        return true;
    }

    /**
     * 金额校验 (实时校验)
     * @param $params
     * @param bool $real_time    实时校验（下单时是否按当前版本信息下单）
     * @return bool
     * */
    private function _checkAmount($params, $real_time = false){
        // 本地环境  或  测试环境支付金额1分时  不校验金额
        if ((YII_ENV == CODE_RUNTIME_TEST && $params['actual_amount'] == 1) || (YII_ENV == CODE_RUNTIME_LOCAL)) {
            return true;
        }
        // 硬件购买信息
        $hardware_info = json_decode($params['shop_product_hardware_info'], true);
        if (!is_array($hardware_info)) {
            $hardware_info = json_decode($hardware_info, true);
        }
        // 匹配版本
        $version = ShopProductVersionModel::findOne($params['shop_product_version_id']);
        if (!$version) {    // 版本匹配失败
            $this->CheckMeg = '产品套餐版本信息错误!';
            return false;
        }
        // 硬件校验
        $productList = explode(',', $version->hardware_ids);
        $TotalProduct = 0;
        if (is_array($hardware_info)) {
            foreach ($hardware_info as $k => $v) {
                if ($real_time && !in_array($k, $productList)) {  // 所提供的硬件不在该版本
                    $this->CheckMeg = '该版本下没有'.$k."硬件!";
                    return false;
                }
                $product = ShopProductModel::findOne($k);
                if ($real_time && ((int)$v['money'] != (int)($product->sell_price))) {  // 硬件售价错误
                    $this->CheckMeg = '硬件'.$k."单价错误!";
                    return false;
                }
                $TotalProduct += (((int)$v['number'])*((int)$v['money']));
            }
        }
        if ($real_time && ((int)$TotalProduct != (int)$params['hardware_purchase_cost'])) {   //  硬件成本计算错误
            $this->CheckMeg = '硬件总费用错误应为：'.$TotalProduct;
            return false;
        }
        // 当前 服务费配置
        if ($real_time) {
            $serviceList = json_decode($version->service_fee, true);
            if (!is_array($serviceList)) {
                $serviceList = json_decode($serviceList, true);
            }
            $spec = $val = [];
            foreach ($serviceList['spec_conf'] as $k => $v) {
                $spec[$k] = $v['spec'];
                $val[$k] = $v['val'];
            }

            if (!in_array($params['software_service_spec'], $spec)) {   //  软件服务规格错误    （不存在的天数）
                $this->CheckMeg = '使用期限不在配置中!';
                return false;
            }
            $k = array_search($params['software_service_spec'], $spec);
            if ((int)$val[$k] != (int)$params['software_service_fee']) {  // 软件服务费错误
                $this->CheckMeg = '使用期限与金额不匹配!';
                return false;
            }
            if ((int)$spec[$k] != (int)$params['software_service_spec']) {  // 软件服务天数错误
                $this->CheckMeg = '使用期限与金额不匹配!';
                return false;
            }
            if ((int)$version->setup_fee != (int)$params['setup_fee']) {    // 开户费错误
                $this->CheckMeg = '开户费匹配错误!';
                return false;
            }
            $TotalAmount = $TotalProduct + $val[$k] + $version->setup_fee;  // 总金额
        } else {
            $TotalAmount = $TotalProduct + $version->setup_fee + $params['software_service_fee'];
        }

        if ((int)$TotalAmount != (int)$params['total_order_amount'] && $params['order_type'] != ShopSalesOrderModel::OFFLINE_ORDER) {  // 总金额错误
            $this->CheckMeg = '总金额匹配失败!';
            return false;
        }
        if ((int)$TotalAmount != (int)$params['actual_amount'] && $params['order_type'] != ShopSalesOrderModel::OFFLINE_ORDER) {  // 支付金额金额匹配失败
            $this->CheckMeg = '支付金额金额匹配失败!';
            return false;
        }
        return true;
    }


    /**
     *  支付结果
     */
    public function actionResultStatus(){
        $rules = [
            [["customer_id", "order_no"], "required"]
        ];
        $params = ($this->checkForm($this->paramsPost, $rules));

        $order = ShopSalesOrderModel::find()
            ->select(ShopSalesOrderModel::$EffectOrderField)
            ->where([
                "money_status" => ShopSalesOrderModel::ENTERED_ACCOUNT,
                "customer_id" => $params['customer_id'],
                "order_no" => $params["order_no"]
            ])->limit(1)->asArray()->one();

        if ($order) {
            return $this->success($order);
        }
        return $this->error(BaseError::USER_NOT_EXISTS, "未收到付款!");
    }
}