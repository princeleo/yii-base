<?php

namespace app\common\services;
use app\common\cache\UserCache;
use app\common\helpers\BaseHelper;
use app\models\agent\AgentAccountTrans;
use app\models\message\BaseMessage;
use app\models\message\BaseMessageInbox;
use app\models\message\BaseMessageTemplate;
use Yii;

/**
 * 统一发送消息service
 * Class MessageService
 * @package app\common\services\script
 */
class AgentTransService {


    /**增加流水
     * @param array $params
     * @return array|int
     */
    public static function create($params = [])
    {
        return  AgentAccountTrans::create($params);

    }
}