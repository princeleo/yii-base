<?php

namespace app\common\vendor\api;

use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\common\services\BaseVariableService;
use app\models\shop\Customer;
use app\models\shop\CustomerUploadUrl;
use Yii;

/**
 * 发送http或https请求, 返回响应内容.
 *
 * @requires cURL
 * @author sangechen
 *
 */
class SpeedPosApi
{

    //上传图片类型
    const PIC_ID_CARD = 1;
    const PIC_BUSINESS = 2;
    const PIC_BANK_CARD = 3;


    public static  function getPic()
    {
        return [
            self::PIC_ID_CARD => 'idcard',
            self::PIC_BUSINESS => 'business',
            self::PIC_BANK_CARD => 'bankcard',
        ];
    }

    /**
     * 支付配置
     */
    const WXPAY_JSAPI = 1; //微信公众号支付
    const WXPAY_APP = 2; //微信APP支付
    const WXPAY_NATIVE = 3; //微信NATIVE支付
    const WXPAY_MICROPAY = 4; //微信扫码支付

    const ALIPAY_JSAPI = 5; //支付宝服务窗支付
    const ALIPAY_APP = 6; //支付宝APP支付
    const ALIPAY_NATIVE = 7; //支付宝扫码支付
    const ALIPAY_MICROPAY = 8; //支付宝刷卡支付

    public static  function getWxPay()
    {
        return [
            self::WXPAY_JSAPI => 'WXPAY.JSAPI',
            self::WXPAY_APP => 'WXPAY.APP',
            self::WXPAY_NATIVE => 'WXPAY.NATIVE',
            self::WXPAY_MICROPAY => 'WXPAY.MICROPAY',
            self::ALIPAY_JSAPI => 'ALIPAY.JSAPI',
            self::ALIPAY_APP => 'ALIPAY.APP',
            self::ALIPAY_NATIVE => 'ALIPAY.NATIVE',
            self::ALIPAY_MICROPAY => 'ALIPAY.MICROPAY'
        ];
    }

    public static  function getAliPay()
    {
        return [
            self::WXPAY_JSAPI => 'ALIPAY.JSAPI',
            self::WXPAY_APP => 'ALIPAY.APP',
            self::WXPAY_NATIVE => 'ALIPAY.NATIVE',
            self::WXPAY_MICROPAY => 'ALIPAY.MICROPAY'
        ];
    }

    private $parameters;
    private $key;
    private $baseUrl;
    public $version = '1.0';


    /**
     * 商户创建
     */
    public function getMchadd($params)
    {
        $urlModel = new CustomerUploadUrl();
        $customerModel = Customer::findOne($params['customer_id']);

        $home_page = strstr($params['homepage'],'http://');

        //预防资料进件表没有图片
        if(!strstr($params['headman_pic'],'http://')){
            $params['headman_pic'] = $urlModel->localToSpeedPos($customerModel->headman_pic);
            Yii::error('speedPosMchAdd | step2 | headman_pic='.$params['headman_pic'],__METHOD__);
        }
        if(!strstr($params['bank_card_pic'],'http://')){
            $params['bank_card_pic'] = $urlModel->localToSpeedPos($customerModel->bank_card_pic);
            Yii::error('speedPosMchAdd | step3 | bank_card_pic='.$params['bank_card_pic'],__METHOD__);
        }

        if(!strstr($params['business_licence_pic'],'http://')){
            $params['business_licence_pic'] = $urlModel->localToSpeedPos($customerModel->business_licence_pic);
            Yii::error('speedPosMchAdd | step4 | business_licence_pic='.$params['business_licence_pic'],__METHOD__);
        }
        if($params['account_type'] == 2){
//            if(empty($params['open_account_pic']) || empty ($params['open_account_num'])){
//                throw new ApiException(BaseError::SAVE_ERROR);
//            }
            if(!strstr($params['open_account_pic'],'http://')){
                $params['open_account_pic'] = $urlModel->localToSpeedPos($customerModel->open_account_pic);
                Yii::error('speedPosMchAdd | step6 | open_account_pic='.$params['open_account_pic'],__METHOD__);
            }
        }


        $apiParams = [
            'shops'=>[
                'shop_name' => isset($params['name']) ? $params['name'] : null,
                'shop_sname' => isset($params['short_name']) ? $params['short_name'] : null,
                'industry_type' => isset($params['indu_id']) ? $params['indu_id'] : null,
                'business_type' =>isset($params['partner_type']) ? $params['partner_type'] : null,
                'account_type' =>isset($params['account_type']) ? $params['account_type'] : null,
                'homepage' => empty($home_page)?'http://'.$params['homepage']:$params['homepage'],
                'headman' =>isset($params['headman']) ? $params['headman'] : null,
                'headman_mobile' => isset($params['headman_mobile']) ? $params['headman_mobile'] : null,
                'headman_idnum' => isset($params['headman_idnum']) ? $params['headman_idnum'] : null,
                'headman_card_pic' => isset($params['headman_pic']) ? $params['headman_pic'] : null,
                'prov' => isset($params['province_id']) ? $params['province_id'] : null,
                'city' =>isset($params['city_id']) ? $params['city_id'] : null,
                'dist' => isset($params['dist_id']) ? $params['dist_id'] : null,
                'addr' => isset($params['address']) ? $params['address'] : null,
                'email' => isset($params['email']) ? $params['email'] : null,
                'tel' => isset($params['mobile']) ? $params['mobile'] : null,
                'businesslicence' => isset($params['business_licence_no']) ? $params['business_licence_no'] : null,
                'legalperson' => isset($params['legalperson']) ? $params['legalperson'] : null,
                'business_pic' => isset($params['business_licence_pic']) ? $params['business_licence_pic'] : null,
                'sub_mch_id' => Yii::$app->params['speed_pos_conf']['code'],
                'is_default_pay' => 0,
                'channal_pay_auth' => 0,
            ],
            'settlesetting'=>[
                'bank_cardno' => isset($params['bank_cardno'])?$params['bank_cardno']: null,
                'bank_owner' => isset($params['open_account_owner'])?$params['open_account_owner']: null,
                'bank_type' => isset($params['bank_code'])?$params['bank_code']: null,
                'bank_branch' => isset($params['bank_branch'])?$params['bank_branch']: null,
                'bank_card_pic' => isset($params['bank_card_pic'])?$params['bank_card_pic']: null,
                'branch_no' => isset($params['bank_branch_code'])?$params['bank_branch_code']: null,
                'prov' => isset($params['province_id'])?$params['province_id']: null,
                'city' => isset($params['city_id'])?$params['city_id']: null,
                'mobile' => isset($params['open_account_mobile'])?$params['open_account_mobile']: null,
                'is_public' => $params['account_type'] ==1 ? $params['account_type']: 0,
                'card_type' =>1,
                'card_no' => isset($params['open_account_num'])?$params['open_account_num']: null,
                'card_pic' => isset($params['open_account_pic'])?$params['open_account_pic']: null,
            ]

        ];

        Yii::error('speedPosMchAdd | step5 | apiParams='.json_encode($apiParams),__METHOD__);
        return $apiParams;
    }

    /**
     * 商户修改
     */
    public function getMchUpdate($params,$mch_id)
    {
        $apiParams = [
            'mch_id'=>$mch_id,
            'shops'=>[
                'shop_name' => isset($params['name']) ? $params['name'] : null,
                'shop_sname' => isset($params['short_name']) ? $params['short_name'] : null,
                'industry_type' => isset($params['indu_id']) ? $params['indu_id'] : null,
                'business_type' =>isset($params['partner_type']) ? $params['partner_type'] : null,
                'account_type' =>isset($params['account_type']) ? $params['account_type'] : null,
                'homepage' => empty($home_page)?'http://'.$params['homepage']:$params['homepage'],
                'headman' =>isset($params['headman']) ? $params['headman'] : null,
                'headman_mobile' => isset($params['headman_mobile']) ? $params['headman_mobile'] : null,
                'headman_idnum' => isset($params['headman_idnum']) ? $params['headman_idnum'] : null,
                'headman_card_pic' => isset($params['headman_pic']) ? $params['headman_pic'] : null,
                'prov' => isset($params['province_id']) ? $params['province_id'] : null,
                'city' =>isset($params['city_id']) ? $params['city_id'] : null,
                'dist' => isset($params['dist_id']) ? $params['dist_id'] : null,
                'addr' => isset($params['address']) ? $params['address'] : null,
                'email' => isset($params['email']) ? $params['email'] : null,
                'tel' => isset($params['mobile']) ? $params['mobile'] : null,
                'businesslicence' => isset($params['business_licence_no']) ? $params['business_licence_no'] : null,
                'legalperson' => isset($params['legalperson']) ? $params['legalperson'] : null,
                'business_pic' => isset($params['business_licence_pic']) ? $params['business_licence_pic'] : null,
            ],
            'settlesetting'=>[
                'bank_cardno' => isset($params['bank_cardno'])?$params['bank_cardno']: null,
                'bank_owner' => isset($params['open_account_owner'])?$params['open_account_owner']: null,
                'bank_type' => isset($params['bank_code'])?$params['bank_code']: null,
                'bank_branch' => isset($params['bank_branch'])?$params['bank_branch']: null,
                'bank_card_pic' => isset($params['bank_card_pic'])?$params['bank_card_pic']: null,
                'branch_no' => isset($params['bank_branch_code'])?$params['bank_branch_code']: null,
                'prov' => isset($params['province_id'])?$params['province_id']: null,
                'city' => isset($params['city_id'])?$params['city_id']: null,
                'mobile' => isset($params['open_account_mobile'])?$params['open_account_mobile']: null,
                'is_public' => $params['account_type'] ==1 ? $params['account_type']: 0,
                'card_type' =>1,
                'card_no' => isset($params['open_account_num'])?$params['open_account_num']: null,
                'card_pic' => isset($params['open_account_pic'])?$params['open_account_pic']: null,
            ]

        ];

        Yii::error('speedPosMchUpdate | apiParams='.json_encode($apiParams),__METHOD__);
        return $apiParams;
    }

    public function getSettingAdd($params)
    {
        $apiParams = [
            'mch_id' => isset($params['speedpos_id'])?$params['speedpos_id']: null,
            'trade_type' => isset($params['trade_type'])?$params['trade_type']: null,
            'calc_rate' => isset($params['rate'])?$params['rate']: null,
            'min_amount' => isset($params['min_amount'])?$params['min_amount']: null,
            'max_amount' => isset($params['max_amount'])?$params['max_amount']: null,
            'day_amount' => isset($params['day_amount'])?$params['day_amount']: null,
        ];
        Yii::error('getSettingAdd | step1 | apiParams='.json_encode($apiParams),__METHOD__);
        return $apiParams;
    }


    public function __construct($channelid = 0, $key = '')
    {
        $this->baseUrl = Yii::$app->params['speed_pos_conf']['url'];
        $this->key = Yii::$app->params['speed_pos_conf']['key'];
        $this->setParameter('channelid', Yii::$app->params['speed_pos_conf']['channel']);
        $this->setParameter('datatype', 'json');

    }

    /**
     * 设置参数
     *
     * @param string $key
     * @param string $value
     */
    public function setParameter($key = '', $value = '')
    {
        if (!is_null($value) && !is_bool($value)) {
            $this->parameters[$key] = $value;
        }
    }

    /**
     * 获取参数值
     *
     * @param $key
     *
     * @return string
     */
    public function getParameter($key)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : '';
    }

    /**
     * 批量设置参数
     *
     * @param array $arr
     */
    public function setParameters($arr = [])
    {
        foreach ($arr as $key => $value) {
            $this->setParameter($key, $value);
        }
    }

    /**
     * @return string
     * @internal param array $params
     */
    public function toUrlParams()
    {
        $buff = "";
        foreach ($this->parameters as $k => $v) {
            if ($k != "sign" && !is_null($v) && $k != '_url' && $k != '_file') {
                $buff .= $k . "=" . (is_array($v) ? json_encode($v) : $v) . "&";
            }
        }
        $buff = trim($buff, "&");

        return $buff;
    }

    /**
     * 生成签名
     *
     * @return string
     */
    public function makeSign()
    {
        // 设置校验时间戳
        !$this->getParameter('_timestamp') && $this->setParameter('_timestamp', time());
        // 设置随机字符串
        !$this->getParameter('_nonce_str') && $this->setParameter('_nonce_str', $this->createNoncestr());
        // 设置版本号
        !$this->getParameter('_version') && $this->setParameter('_version', $this->version);
        //签名步骤一：按字典序排序参数
        ksort($this->parameters);
        $string = $this->toUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . '&' . $this->key;
        //签名步骤三：MD5加密
        $result = md5($string);
        //所有字符转为大写
        $sign = strtoupper($result);

        return $sign;
    }

    /**
     * 作用：产生随机字符串，不长于32位
     *
     * @param int $length
     *
     * @return string
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     *    作用：将xml转为array
     *
     * @param $xml
     *
     * @return mixed
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $array_data;
    }


    /**
     * 返回结果
     *
     * @param int    $error
     * @param string $msg
     * @param array  $data
     */
    public function toResult($error = 0, $msg = '', $data = [])
    {
        $result = [
            'error' => $error,
            'msg'   => $msg ? $msg : ($error == 0 ? 'success' : ''),
            'data'  => $data
        ];

        exit(json_encode($result));
    }

    /**
     * 请求接口
     *
     * @param string $api
     * @param array  $data
     * @param string $method
     * @param bool   $debug
     * @param bool   $resultAndClear
     *
     * @return mixed
     */
    public function requestApi($api = '', $data = [], $debug = false, $resultAndClear = true)
    {
        $this->setParameter('data', json_encode($data));
        $sign = $this->makeSign();
        $this->setParameter('sign', $sign);
        if (isset($data['_file'])) {
            $this->parameters['_file'] = $data['_file'];
        }
        $result = $this->doRequest($this->baseUrl.$api, $this->parameters);

        $result = $this->getParameter('datatype') == 'xml' ? $this->xmlToArray($result) : json_decode($result, true);

        Yii::error('接口URL='.$this->baseUrl.$api,__METHOD__);

        return $result;
    }

    /** http请求
     *
     * @param        $url
     * @param array  $params
     *
     * @return mixed
     */
    protected function doRequest($url, $params = [])
    {
        Yii::error($params,'speedPos-doRequest');

        if (!function_exists('curl_init')) {
            exit('Need to open the curl extension');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0); //展示响应头
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);//设置连接等待时间,0不等待
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);


        $output = curl_exec($ch);

        $curl_getinfo = curl_getinfo($ch);
        $error = curl_error($ch);
        if(!empty($error)){
            Yii::error($error .' | '.json_encode($curl_getinfo),'speedPos-doRequest');
        }

        curl_close($ch);

        return $output;
    }
}
