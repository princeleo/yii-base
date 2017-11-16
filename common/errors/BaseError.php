<?php


namespace app\common\errors;

class BaseError
{
    const SUCC									= 0;			//成功
    const UNKONWN_ERR							= -100000;		//未知错误
    const PARAMETER_ERR							= -100001;		//参数错误
    const NOT_LOGIN								= -100002;		//未登陆
    const USER_NOT_EXISTS						= -100003;		//用户不存在
    const USER_AUTH_ERR                        = -100004;      //账号或密码失败
    const SVR_ERR								= -100005;		//操作后台失败
    const INVALID_REQUEST						= -100006;		//无效请求
    const UNKNOW_CLIENT							= -100007;		//未知客户端
    const CLIENT_VERSION_ERROR					= -100008;		//客户端版本错误
    const EXCEPTION_REQUEST                     = -100009;      //异常请求
    const CONFIG_ERR                            = -100010;      //配置错误
    const API_URL_MAP                           = -100011;      //API 接口不存在
    const API_TIMEOUT                           = -100012;      //API请求超时
    const REQUEST_ERROR                         = -100013;      // 网络请求失败，请稍后重试
    const CAPTCHA_ERROR                         = -100015;      //验证码错误
    const UIN_NOT_LOGIN                         = -100016;      //用户未登录
    const NO_AUTH                               = -100017;      //无操作权限
    const USER_EXISTS                           = -100018;      //用户已经存在
    const NOT_FILE_TYPE                         = -100019;      //未知文件类型
    const UPLOAD_FILE_ERR                       = -100020;      //文件上传失败
    const NOT_ALLOW_FILE_TYPE                   = -100021;      //不支持的文件类型
    const NAME_HAS_BEEN_ALREADY                 = -100022;      //名称不能重复
    const SAVE_ERROR                            = -100023;      //保存出错
    const NOT_DATA_TO_FIND                      = -100024;      //查找不到此数据
    const REPEAT_ACTION_ERR                     = -100025;      //操作失败或重复操作
    const CUSTOMER_MOBILE_EXIST                 = -100026;      //手机号已经被使用
    const CUSTOMER_RATE_NOT_SET                 = -100027;      //请设置汇率后提单
    const CUSTOMER_PROMOTION_NOT_EXIST          = -100028;      //无此地推员，请按照正常流程录入数据
    const CUSTOMER_UPLOAD_PID_SIZE              = -100029;      //图片上传不能超过500K
    const FILE_NOT_EXIST                        = -100030;      //无法找到文件
    const FILE_NOT_READ                         = -100031;      //无法打开文件，或者文件创建失败
    const CUSTOMER_ACCOUNT_EXIST                = -100032;      //商户号已存在
    const ACCOUNT_AUTH_FAILED                   = -100033;      //验证失败，请核对信息后录入
    const CUSTOMER_ACCOUNT_CREATE               = -100034;      //请录入商户号
    const AGENT_AUTHORIZE_ERROR                 = -100035;      //授权号重复


    const USER_PWD_ERROR                        = -200001;//密码错误
    const USER_STATUS_FAIL                      = -200002;//账号失效


    const SETTLE_RULE_EXISTS                    = -300001;//服务商分佣项已存在
    const SETTLE_RULE_TIME_CLASH                = -300002;//服务商分佣项时间冲突
    const SETTLE_RULE_CLASH                     = -300012;//服务商分佣规则冲突
    const SETTLE_RULE_NOT_APP                   = -300013;//服务费设置不在代理范围之内
    const SETTLE_RULE_NOT_SERVICE_TIME          = -300014;//不在服务时间范围内
    const AGENT_NOT_SUBMIT_AUDIT                = -300003;//服务商未提交开户
    const AGENT_STATUS_NOT_PASS                 = -300004;//服务商未审核通过
    const AGENT_NOT_EXISTS                      = -300005; //服务商未存在
    const AGENT_SHOP_NOT_ALLOT                  = -300006;//地推商户未分配完
    const AGENT_SCOPE_REPEAT                    = -300007;//服务商代理范围重复

    const MESSAGE_AGENT_NOT_EXIST               = -400001;//通知未设置收信人
    const USER_MOBILE                           = -400002;//手机号码已经存在
    const MESSAGE_TEMP_ID_NOT_FIND              = -400003;//消息模板不存在
    const MESSAGE_PUSH_TYPE_ERR                 = -400004;//消息类型错误
    const MESSAGE_NOT_EXIST                     = -400005;//消息未存在
    const MESSAGE_SEND_ERR                      = -400006;//消息发送失败

    const PAY_PARAMS_ERROR                      = -600001;// 支付参数错误
    const CREATE_PAY_FAIL                       = -600002;// 创建支付失败
    const THE_QUERY_FAILS                       = -600003;// 查询失败
    const NOT_ORDER                             = -600004;// 商家没有订单请下单
    const ORDER_AMOUNT_ERROR                    = -600005;// 订单金额错误
    const PAYMENT_HAS_BEEN                      = -600006;// 商家已付款

    const DATA_SYNCHRONIZATION_FAILED           = -900001;// 信息同步失败

    public static $ERR_CODE_MAP = array(
        self::SUCC									=>'成功',
        self::UNKONWN_ERR							=>'未知错误',		//如果没有配置对应的code，则默认用这个
        self::PARAMETER_ERR							=>'参数错误',
        self::NOT_LOGIN								=>'未登陆',
        self::USER_NOT_EXISTS						=>'用户不存在',
        self::USER_AUTH_ERR                         =>'账号或密码有误',
        self::SVR_ERR								=>'操作后台失败',
        self::INVALID_REQUEST						=>'无效请求',
        self::UNKNOW_CLIENT							=>'未知客户端',
        self::CLIENT_VERSION_ERROR					=>'客户端版本错误',
        self::EXCEPTION_REQUEST					    =>'异常请求',
        self::CONFIG_ERR                            =>'配置错误',
        self::API_URL_MAP                           =>'接口不存在',
        self::API_TIMEOUT                           => '请求超时',
        self::REQUEST_ERROR                         =>'网络请求失败，请稍后重试',
        self::CAPTCHA_ERROR                         =>'验证码错误或已失效',
        self::UIN_NOT_LOGIN                         =>'用户未登录',
        self::NO_AUTH                               =>'无操作权限',
        self::USER_EXISTS                           =>'用户已经存在',
        self::NOT_FILE_TYPE                         =>'未知文件类型',
        self::UPLOAD_FILE_ERR                       =>'文件上传失败',
        self::NOT_ALLOW_FILE_TYPE                   =>'请上传规定类型的文件',//（目前只支持jpg、gif、bmp、png、mp3、rar）
        self::NOT_DATA_TO_FIND                      =>'查找不到此数据',
        self::REPEAT_ACTION_ERR                     =>'操作失败或重复操作',
        self::CUSTOMER_MOBILE_EXIST                 =>'手机号已经被使用',
        self::CUSTOMER_RATE_NOT_SET                 =>'请设置汇率后提单',
        self::CUSTOMER_PROMOTION_NOT_EXIST          =>'无此地推员，请按照正常流程录入数据',
        self::CUSTOMER_UPLOAD_PID_SIZE              =>'图片上传不能超过500K',
        self::FILE_NOT_EXIST                        =>'文件不存在',
        self::FILE_NOT_READ                         =>'无法打开文件，或者文件创建失败',
        self::CUSTOMER_ACCOUNT_EXIST                =>'商户号已存在',
        self::ACCOUNT_AUTH_FAILED                   =>'验证失败，请核对信息后录入',
        self::CUSTOMER_ACCOUNT_CREATE               =>'请录入商户号',
        self::AGENT_AUTHORIZE_ERROR                 =>'授权号重复',

        self::USER_PWD_ERROR                        =>'密码错误',
        self::USER_STATUS_FAIL                      =>'账号已失效',
        self::NAME_HAS_BEEN_ALREADY                 =>'名称重复或者手机号重复',
        self::SAVE_ERROR                            =>'保存出错',

        self::SETTLE_RULE_EXISTS                    =>'此分佣项已存在',
        self::SETTLE_RULE_TIME_CLASH                =>'服务费设置时间冲突',
        self::SETTLE_RULE_NOT_SERVICE_TIME          =>'服务费设置不在服务有效期内',
        self::SETTLE_RULE_CLASH                     =>'同一平台同一服务类型同一时间段内只能配置一种服务费',
        self::SETTLE_RULE_NOT_APP                   =>'服务费设置不在代理范围之内',
        self::AGENT_NOT_SUBMIT_AUDIT                =>'服务商未提交开户',
        self::AGENT_STATUS_NOT_PASS                 =>'服务商未审核通过',
        self::AGENT_NOT_EXISTS                      =>'服务商不存在',
        self::AGENT_SHOP_NOT_ALLOT                  =>'商户未分配完',
        self::AGENT_SCOPE_REPEAT                    =>'服务商代理范围重复',

        self::MESSAGE_AGENT_NOT_EXIST               =>'发布失败，请先设置收信人',
        self::USER_MOBILE                           =>'手机号码已经存在',
        self::MESSAGE_TEMP_ID_NOT_FIND              =>'消息模板不存在',
        self::MESSAGE_PUSH_TYPE_ERR                 =>'消息类型错误',

        self::PAY_PARAMS_ERROR                      => '支付参数错误!',
        self::CREATE_PAY_FAIL                       => '创建支付失败',
        self::THE_QUERY_FAILS                       => '查询支付失败',
        self::NOT_ORDER                             => '商家没有订单，请重新下单',
        self::ORDER_AMOUNT_ERROR                    => '订单金额错误',
        self::PAYMENT_HAS_BEEN                      => '商家已付款',

        self::DATA_SYNCHRONIZATION_FAILED           => '信息同步失败',
    );

    /**
     * 获取对应的错误信息
     * refer:config/errors.php
     * @param $code
     */
    public static function getError($code){
        return isset(self::$ERR_CODE_MAP[$code]) ? self::$ERR_CODE_MAP[$code] : self::$ERR_CODE_MAP[self::UNKONWN_ERR];
    }
}
