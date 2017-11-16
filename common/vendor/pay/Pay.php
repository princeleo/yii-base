<?php

namespace app\common\vendor\pay;

use app\common\helpers\FormatHelper;
use app\common\log\Log;
use app\common\vendor\request\HttpClient;
use yii;


class Pay
{
    // 支付类型
    const WX = 1;
    const ZFB = 2;
    public static $payService = [       // 支付接口地址
        self::WX        => "pay.weixin.native",
        self::ZFB       => "pay.alipay.native"
    ];
    const PAY_INFO_SERVICE = "unified.trade.query";

    // 威富通支付 url
    const SWIFT_PASS_PAY_URL = 'https://pay.swiftpass.cn/pay/gateway';

    // 二维码 失效时间 time_expire (s)秒
    const TIME_EXPIRE = 3600;

    /**
     *  威富通支付
     * @param int $payType  支付类型
     * @param array $params 支付参数
     * @return array
     */
    public static function SwiftPassPay($payType, $params) {
        Log::info("PayBegin:[No.".$params['order_no']."]", $params);
        $payData = [
            "service"       => self::$payService[$payType],                 //接口类型
            "charset"       => "UTF-8",                                     //  字符集
            "sign_type"     => "MD5",                                       //  签名类型
            "mch_id"        => Yii::$app->params['SwiftPass']['mch_id'],    // 商户号
            "out_trade_no"  => $params['order_no'],                         // 销售订单号
            "body"          => $params['body'],                             // 商品描述
            "mch_create_ip" => $_SERVER['SERVER_ADDR'],                     // 本机ip
            "notify_url"    => $params['notify_url'],                       // 支付结果回调地址
            "time_start"    => $params['time_start'],                       // 订单生成时间
            "time_expire"   => $params['time_expire'],                      // 订单失效时间
            "nonce_str"     => (string)md5(time().rand(0,12)),              // 随机串
        ];

        // 总金额
        if (YII_ENV == CODE_RUNTIME_ONLINE || YII_ENV == CODE_RUNTIME_TEST) {
            $payData['total_fee'] = (int)$params['actual_amount'];          //  正式 || 测试环境
        } else {
            $payData['total_fee'] = 1;
        }
        $payData['sign'] = self::getSign($payData);
        Log::info("PayPost:[No.".$params['order_no']."]", $payData);
        // 转 XML
        $payData = FormatHelper::ArrayToXml($payData);
        HttpClient::CallCURLPOST(self::SWIFT_PASS_PAY_URL, $payData, $resp, array(), 0);
        $retData = FormatHelper::XmlToArray($resp);
        Log::info("PayResults:[No.".$params['order_no']."]", $retData);
        return $retData;
    }

    /**
     *  生成 sign
     * @param array $params
     * @return string $sign
     */
    public static function getSign($params) {
        if (isset($params['sign'])) {   // 去除sign
            unset($params['sign']);
        }
        // ascii 排序
        ksort($params);
        // 生成  sign
        $sign = FormatHelper::ArrayToQueryString($params);
        $sign = strtoupper(md5($sign."&key=".Yii::$app->params['SwiftPass']['merchant_key']));
        return $sign;
    }

    /**
     *  威富通支付结果
     * @param array $params 支付参数
     * @return array
     */
    public static function GetSwiftPassPayInfo($params) {
        Log::info("PayInfoBegin:[No.".$params['order_no']."]", $params);
        $payData = [
            "service"           => self::PAY_INFO_SERVICE,                      //接口类型
            "charset"           => "UTF-8",                                     //  字符集
            "sign_type"         => "MD5",                                       //  签名类型
            "mch_id"            => Yii::$app->params['SwiftPass']['mch_id'],    // 商户号
            "out_trade_no"      => $params['order_no'],                         // 销售订单号
            "nonce_str"         => (string)md5(time().rand(0,12)),              // 随机串
        ];
        if (!empty($params['transaction_id'])) {
            $payData["transaction_id"] = $params['transaction_id'];              // 平台订单号
        }
        $payData['sign'] = self::getSign($payData);

        Log::info("PayInfoPost:[No.".$params['order_no']."]", $payData);
        // 转 XML
        $payData = FormatHelper::ArrayToXml($payData);
        HttpClient::CallCURLPOST(self::SWIFT_PASS_PAY_URL, $payData, $resp, array(), 0);
        $retData = FormatHelper::XmlToArray($resp);
        Log::info("PayInfoResults:[No.".$params['order_no']."]", $retData);
        return $retData;
    }
}