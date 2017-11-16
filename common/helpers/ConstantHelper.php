<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/20
 * Time: 10:44
 */
namespace app\common\helpers;

class ConstantHelper
{
    /**
     * 平台
     */
    const PLATFORM_ZHCT = "zhct"; //智慧餐厅
    const PLATFORM_VIKDUO = "vikduo"; //微客多
    const PLATFORM_WSH = "wsh"; //微商户
    const PLATFORM_ELEPHANT = 100001;//大象平台
    public static $platforms = [
        self::PLATFORM_VIKDUO,
        self::PLATFORM_ZHCT,
        self::PLATFORM_ELEPHANT,
    ];

    /**
     * 平台映射关系
     * @var array
     */
    public static $appMap = [
        1 => self::PLATFORM_WSH,
        2 => self::PLATFORM_VIKDUO,
        3 => self::PLATFORM_ZHCT,
        100001 => self::PLATFORM_ELEPHANT,
    ];

    /**
     * 来源与分佣平台映射关系
     * @var array
     */
    public static $appPlatformMap = [
        self::PLATFORM_WSH => self::PLATFORM_VIKDUO,  //wsh => vikduo
        self::PLATFORM_VIKDUO => self::PLATFORM_VIKDUO, //vikduo => vikduo
        self::PLATFORM_ZHCT => self::PLATFORM_ZHCT, //zhct => zhct
    ];

    /**
     * 盛灿成本费率(%)
     * @var array
     */
    public static $sctekRates = [
        1 => 0.22, //中信
        2 => 0.28, //浦发
    ];
}