<?php
/**
 * Author: Ivan Liu
 * Date: 2016/12/19
 * Time: 20:48
 */

namespace app\common\services\script;

use app\common\helpers\ConstantHelper;
use app\common\services\AgentSettleService;
use app\models\base\SettleGroup;
use app\models\shop\ConsumeOrder;
use app\models\shop\ShopBase;
use Yii;
use yii\base\Exception;

/**
 * Class GgtService
 * @package app\common\services\script
 */
class GgtService extends BaseService {
    /**
     * 应用id
     * @var string
     */
    private $appId = 'LHSy5CWGhIucn0Uq';

    /**
     * @var string
     */
    private $appKey = '%7x$9348lyc2pdnbnf1ls0hc784b76q@';

    /**
     * 分页数
     */
    const GGT_PAGE_SIZE = 500;

    /**
     * ggt系统平台
     */
    const GGT_PLATFORM_WSH = 1;
    const GGT_PLATFORM_VIKDUO = 2;

    /**
     * 当前时间
     * @var
     */
    private $nowTime;

    /**
     * 每日消费流水接口
     */
    const ROUTE_CONSUME_WATER_DAILY = '/apis/daily-costs';

    private function httpPost($uri, $params)
    {
        $timestamp = time();
        $ticket = md5($this->appId.$this->appKey.$timestamp); //md5(appid.appkey.timestamp)
        $default = [
            'timestamp' => $timestamp,
            'ticket' => $ticket,
        ];
        $params = array_merge($default, $params);
        $url = trim(Yii::$app->params['ggtApiUrl'], '/').$uri;
        /*print_r($url);
        print_r($params);
        exit;*/
        return $this->httpCurlPost($url, $params);
    }

    /**
     * @param $params
     * @return mixed|string
     * @throws \app\common\exceptions\ScriptException
     */
    public function getConsumeDaily($params)
    {
        return $this->httpPost(self::ROUTE_CONSUME_WATER_DAILY, $params);
    }

    /**
     * @param $data
     * @return bool
     */
    public function saveConsumeWater($data, $paltform)
    {
        if (empty($data) || !is_array($data)) {
            return true;
        }
        !empty($this->nowTime) || $this->nowTime = time();

        $consumeOrderFields = 'consume_date,app_id,agent_id,shop_id,cost,exposure,click,ctr,cpm,cpc,created,rule_id,settle_rate,commission,src';
        $consumeOrderSql = 'REPLACE INTO '.ConsumeOrder::tableName().' ('.$consumeOrderFields.') values  ';

        $agentSettleService = new AgentSettleService();
        $appMap = ConstantHelper::$appMap;
        $appPlatformMap = ConstantHelper::$appPlatformMap;
        $orderVals = '';
        foreach($data as $item) {
            if (empty($item['wsh_shop_id'])) {
                Yii::info('ConsumeOrder_getShopInfo | null | shop_id['.$item['wsh_shop_id'].']', __METHOD__);
                continue;
            }
            $shopInfo = ShopBase::findOne(['shop_id' => $item['wsh_shop_id']]);
            if (empty($shopInfo)) {
                Yii::info('ConsumeOrder_getShopInfo | empty | shop_id['.$item['wsh_shop_id'].']', __METHOD__);
                $shop_id = $item['wsh_shop_id'];
                $agent_id = 0;
            } else {
                $shop_id = $shopInfo['shop_id'];
                $agent_id = $shopInfo['agent_id'];
            }
            $consume_date = strtotime($item['date']);
            $app_id = $appPlatformMap[$appMap[$paltform]];
            //计算分佣金额
            $commission = $agentSettleService->getCommission($agent_id, 0, '', 0,$item['cost'], $consume_date, SettleGroup::SETTLE_TYPE_CONSUME, $app_id);
            if (empty($commission)) {
                $commission = [0,0,0];
            }

            $orderVals .= "('".$consume_date."','{$app_id}','{$agent_id}','{$shop_id}','{$item['cost']}','{$item['impression']}','{$item['click']}','{$item['ctr']}','{$item['cpm']}','{$item['cpc']}','{$this->nowTime}',{$commission[2]},{$commission[0]},{$commission[1]},'{$appMap[$paltform]}'),";
        }
        $orderVals = trim($orderVals, ',');
        if ($orderVals) {
            $consumeOrderSql .= $orderVals.';';
            $transaction = ConsumeOrder::getDb()->beginTransaction();
            try{
                Yii::info('', __METHOD__);
                Yii::$app->db->createCommand($consumeOrderSql)->query();
                Yii::info('ConsumeOrder SQL exec | success | '.$consumeOrderSql, __METHOD__);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::info('ConsumeOrder SQL exec | fail | '.$consumeOrderSql, __METHOD__);
                Yii::info('saveConsumeWater SQL exec | exception | '.$e->getMessage(), __METHOD__);
            }
        }
    }
}