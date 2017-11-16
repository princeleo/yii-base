<?php

namespace app\modules\gateway\controllers;

use app\common\log\Log;
use app\common\ResultModel;
use app\common\helpers\FormatHelper;
use app\common\vendor\pay\Pay;
use app\models\baseboss\ShopSalesOrderPayInfoModel;
use app\models\shop\Customer;
use Yii;
use app\models\baseboss\ShopSalesOrderModel;


class PayInfoController extends BaseController{
    public $layout  = false;
    public $DefaultModel;

    public function init(){
        $this->resultModel = new ResultModel();
        $this->paramsPost = !is_array(json_decode(Yii::$app->request->rawBody,true)) ? Yii::$app->request->post() : json_decode(Yii::$app->request->rawBody,true);
        $input = file_get_contents('php://input');
        if (empty($this->paramsPost) && !empty($input)) {$this->paramsPost = $input; unset($input);}
        $this->debug = isset($this->paramsPost['debug']) ? true : false;
        $this->DefaultModel = new ShopSalesOrderModel();
    }

    /**
     * 威富通支付信息回调处理
     * */
    public function actionSwiftPassPayInfo(){
        if (!empty($this->paramsPost) && FormatHelper::_isXml($this->paramsPost)) {
            $PayInfo = FormatHelper::XmlToArray($this->paramsPost);
            Log::info("SwiftPassPayInfo:Begin", $PayInfo);
            // 基础校验
            if ((isset($PayInfo['status']) && $PayInfo['status'] == 0) && (isset($PayInfo['result_code']) && $PayInfo['result_code'] == 0) && isset($PayInfo['sign'])) {
                $sign = Pay::getSign($PayInfo);
                Log::info("SwiftPassPayInfo:sign", $sign);
                if ($sign == $PayInfo['sign'] && $PayInfo['pay_result'] == 0) {
                    $this->DefaultModel = ShopSalesOrderModel::findOne(["order_no" => $PayInfo['out_trade_no']]);
                    if ((int)$this->DefaultModel->actual_amount == $PayInfo['total_fee']) {
                        $transaction= Yii::$app->db->beginTransaction();//创建事务
                        if ($this->DefaultModel->actual_amount == $PayInfo['total_fee']) {
                            $PayInfo['order_no'] = $this->DefaultModel->order_no;
                            $PayInfo['id'] = $this->DefaultModel->id;
                            if (ShopSalesOrderPayInfoModel::SucceedOrder($PayInfo) && ShopSalesOrderModel::SucceedOrder($PayInfo)){
                                $customer = Customer::findOne($PayInfo['id']);
                                if ($customer && $customer->version_id == $this->DefaultModel->shop_product_version_id) {
                                    Customer::_setOrderId($PayInfo['id'], $PayInfo['order_no']);
                                }
                                $transaction->commit();
                                exit("success");
                            } else {
                                Log::error("SwiftPassPayInfo : Save Error", $PayInfo);
                            }
                        } else {
                            Log::error("SwiftPassPayInfo : Amount Error", $PayInfo);
                        }
                        $transaction->rollBack();
                    } else {
                        Log::error("SwiftPassPayInfo : Total Error", $PayInfo);
                    }
                } else {
                    Log::error("SwiftPassPayInfo : Check Failed", $PayInfo);
                }
            } else {
                Log::error("SwiftPassPayInfo : Pay Failed", $PayInfo);
            }
        } else {
            Log::error("SwiftPassPayInfo : Null");
        }
        exit("fail");
    }
}