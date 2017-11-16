<?php

namespace app\common\services;

use app\common\cache\RedisCache;
use app\common\errors\BaseError;
use app\common\exceptions\ApiException;
use app\models\base\BaseVariable;
use yii;


class BaseVariableService
{

    const ELEPHANT_SUPPLIES_URL = 'ELEPHANT_SUPPLIES_URL';//大象点餐物料下载链接
    const CUSTOMER_HOMEPAGE = 'CUSTOMER_HOMEPAGE'; //商户开户默认网址
    const CUSTOMER_EMAIL = 'CUSTOMER_EMAIL';//商户开户默认邮箱
    const DAILY_REPORT_EMAIL_MAIN = 'DAILY_REPORT_EMAIL_MAIN'; //每日交易数据主收件人
    const DAILY_REPORT_EMAIL_LIST = 'DAILY_REPORT_EMAIL_LIST'; //每日交易数据抄送收件人
    const DAILY_REPORT_API_URL = 'DAILY_REPORT_API_URL'; //每日交易数据API接口

    public function getVariable($key,$flag=false)
    {
        if(empty($key)){
            return false;
        }

        $redisKey = BaseVariable::tableName().'_'.$key;
        $rules = RedisCache::get($redisKey);
        if(empty($rules)){
            $rules = (new BaseVariable())->find()->where(['key'=>$key])->asArray()->one();
            if(empty($rules)){
                return false;
            }
            RedisCache::set($redisKey,$rules);
        }
        if(!$flag){
            return $rules['value'];
        }
        return $rules;
    }


    /**
     * 清除对应的缓存
     * @param $key
     * @return bool
     */
    public function delVariable($key)
    {
        $redisKey = BaseVariable::tableName().'_'.$key;
        RedisCache::del($redisKey);
        return true;
    }

}