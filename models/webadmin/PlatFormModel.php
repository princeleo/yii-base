<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class PlatFormModel extends PublicModel
{
    // 平台配置
    const PHONE_WEBSITE = 1;  // 微客多手机官网
    const ELEPHANT_WEBSITE = 2;  // 大象官网
    const SCTEK_WEBSITE = 3;  // 盛灿官网
    public static $PlatFormList = [
        self::PHONE_WEBSITE => "手机官网",
        self::ELEPHANT_WEBSITE => "大象官网",
        self::SCTEK_WEBSITE => "盛灿官网",
    ];


    // 平台ID
    const ELEPHANT = 100001;  // 大象平台
    const SCTEK = 'sctek';    // 盛灿官网
    public static $AppList = [
        self::ELEPHANT => "大象平台",
        self::SCTEK => "盛灿官网",
    ];

    // 平台代号对应 platform_id
    public static $AppToPlatform = [
        self::ELEPHANT  => self::ELEPHANT_WEBSITE,
        self::SCTEK     => self::SCTEK_WEBSITE,
    ];

    /*
     * 设置平台代号
     * */
    public static function _setPlatForm(&$params){
        if (!empty($params['app_id'])) {
            $params['platform'] = $params['app_id'];
        }
        if (!empty($params['platform'])) {
            $params['platform_id'] = $params['platform'];
            if (isset(PlatFormModel::$AppToPlatform[$params['platform']])) {
                $params['platform_id'] = PlatFormModel::$AppToPlatform[$params['platform']];
            }
        } else {
            $params['platform_id'] = PlatFormModel::PHONE_WEBSITE;
        }
    }
}