<?php
/**
 * Author: leo.zou
 * Date: 2017/03/22
 * Time: 15:18
 * boss 4.0 大象平台流水
 */

namespace app\modules\script\controllers;

use app\models\datacenter_statistics\ScriptLog;
use app\models\datacenter_source\ElephantOrderModel;
use app\models\datacenter_statistics\OperationalDataModel;
use yii\base\ErrorException;
use Yii;


/**
 * 数据中心
 * 大象商户运营数据统计
 * Class ElephantController
 * @package app\modules\script\controllers
 */
class ElephantController extends BaseController {
    public $TimeInterval = 86400; // 时间间隔  默认一天
    public $Model;  // Model
    public $Data = [];   // Data


    /**
     * 运营数据统计
     * @param null $EndTime
     * @param null $StartTime
     * 调用方式： php yii <route>
     * 例子：php yii script/elephant/operational-data
     */
    public function actionOperationalData($EndTime = null, $StartTime = null){
        if (!empty($StartTime) && !empty($EndTime)) {   // 指定时间统计
            $this->_EverySingleDay($StartTime, $EndTime, $this, 'actionOperationalData');
            exit;
        }

        if (empty($EndTime)) {  // 未传入时间则只统计昨日数据
            $EndTime = strtotime(date('Y-m-d 00:00:00', time()));
        }

        $this->Data = [];   // 初始化
        $StartTime = $EndTime - $this->TimeInterval;
        $StartTime_3 = $StartTime - (2 * $this->TimeInterval);  // 最近三日 起始时间
        $StartTime_30 = $StartTime - (29 * $this->TimeInterval);  // 最近三十日 起始时间

        $this->Model = new ElephantOrderModel();
        $domainList = $this->Model->__domainList($EndTime); // 截至时间内所有的商户和区域对应关系

        // 总商户量
        $ShopCount = $this->Model->__shopCount($EndTime);   // 已激活的商户列表
        $this->Recombination($domainList, $ShopCount, "shop_count");    // 记录

        // 总商户量
        $NewShop = $this->Model->__newShop($EndTime, $StartTime);   // 新增商户
        $this->Recombination($domainList, $NewShop, "new_shop");    // 记录

        // 未激活商户数量
        $ActivatedShopList = $this->Model->__activatedShopList($EndTime);   // 已激活的商户列表
        $NoActivatedShopList = $this->Model->__noActivatedShopList($EndTime, $ActivatedShopList, $domainList);  // 未激活商户列表
        $this->Recombination($domainList, $NoActivatedShopList, "no_activated");    // 记录

        if (!empty($ActivatedShopList)) {   // 没有激活的商户
            // 激活商户的当日订单数量列表
            $ShopOrderNumberList = $this->Model->__shopOrderNumber($StartTime, $EndTime, $ActivatedShopList);
            if (!empty($ShopOrderNumberList)) {
                $OpenAsUsual = $this->Model->__openAsUsual($ShopOrderNumberList);   // 正常营业的商户列表
                $this->Recombination($domainList, $OpenAsUsual, "open_as_usual");
                $Active = $this->Model->__active($ShopOrderNumberList);   // 活跃的商户列表
                $this->Recombination($domainList, $Active, "active");
            }

            // 激活商户的最近三十日订单数量列表
            $ShopOrderNumberList_30 = $this->Model->__shopOrderNumber($StartTime_30, $EndTime, $ActivatedShopList);
            $ShopOrderNumberList_3 = $this->Model->__shopOrderNumber($StartTime_3, $EndTime, $ActivatedShopList);
            $ShopOrderNumberList = $this->Model->__shopOrderNumber($StartTime, $EndTime, $ActivatedShopList);

            $RunAway = $this->Model->__runAway($ShopOrderNumberList_30, $ActivatedShopList);    // 流失商户列表
            $this->Recombination($domainList, $RunAway, "run_away");

            $SilentBusiness = $this->Model->__silentBusiness($ShopOrderNumberList_30, $ShopOrderNumberList_3);    // 沉默营业商户列表
            $this->Recombination($domainList, $SilentBusiness, "silent_business");

            $NoBusiness = $this->Model->__noBusiness($ShopOrderNumberList_3, $ShopOrderNumberList);    // 暂无营业数量商户列表
            $this->Recombination($domainList, $NoBusiness, "no_business");
        }

        $scr['controller'] = 'Elephant';
        $scr['action'] = 'OperationalData';
        $scr['datetime'] = date('Y-m-d H:i:s', $StartTime);
        $scr['result'] = '数据为空';
        $scr['data'] = null;
        $scr['created'] = time();

        if (!empty($this->Data)) {
            foreach ($this->Data as $k => &$v) {
                ksort($v);
                $v['date'] = date('Y-m-d H:i:s', $StartTime);
            }
            $key = array_keys($this->Data)[0];

            if (!$this->_dataIsRepeated($this->Data[$key]['date'], new OperationalDataModel())) {
                OperationalDataModel::deleteAll(["date" => date('Y-m-d H:i:s', $StartTime)]);
            }

            // 获取 字段名
            $field = array_keys($this->Data[$key]);
            $tran = Yii::$app->db->beginTransaction();
            try {
                OperationalDataModel::batchInsert(OperationalDataModel::tableName(), $field, $this->Data);
                $tran->commit();
                $scr['result'] = '数据统计成功！';
            } catch (ErrorException $e) {
                $tran->rollBack();
                $scr['result'] = '数据统计失败！';
                $scr['data'] = json_encode($this->Data);
            }
        }

        $count = ScriptLog::find()->select('count(*) nun')
            ->where(["controller" => 'Elephant', "action" => "OperationalData", "datetime" => $scr['datetime'], "result" => '数据统计失败！'])
            ->asArray()
            ->one();
        if ($count['nun'] > 5) {
            Yii::error("脚本：（AgentReport/AgentOrderData）多次执行失败！");
            exit("脚本：（AgentReport/AgentOrderData）多次执行失败！");
        }

        $key = array_keys($scr);
        $dd[] = $scr;

        ScriptLog::batchInsert(ScriptLog::tableName(), $key, $dd);
    }


    /**
     * 组装运营数据
     * @param array $domainList 区域列表
     * @param array $data 基础数据
     * @param string $Name 组装的数据名称
     * @return array
     */
    private function Recombination($domainList, $data, $Name){
        // 组装数据
        if (!empty($domainList) && !empty($data)) {
            foreach ($domainList as $k => $v) {
                foreach ($data as $kk => $vv) {
                    if (!isset($data[$kk]["domain_id"])) {
                        $data[$kk]["domain_id"] = 0;
                    }
                    if ($v["shop_id"] == $vv["shop_id"]) {
                        $data[$kk]["domain_id"] = $v["domain"];
                    }
                }
            }
        }

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $v['promotion_id'] = empty($v['promotion_id']) ? 0 : $v['promotion_id'];
                $v['domain_id'] = !isset($v['domain_id']) ? 0 : $v['domain_id'];
                if (!isset($this->Data[$v['promotion_id']."_".$v['domain_id']])) {
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["app_id"]           = 100001;
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["agent_id"]         = $v["agent_id"];
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["promotion_id"]     = $v["promotion_id"];
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["domain_id"]        = !empty($v["domain_id"]) ? $v['domain_id'] : 0;
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["shop_count"]       =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]['new_shop']         =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["no_activated"]     =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["open_as_usual"]    =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["active"]           =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["no_business"]      =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["silent_business"]  =
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["run_away"]         = 0;
                    $this->Data[$v['promotion_id']."_".$v['domain_id']]["created"]          = time();
                }
                $this->Data[$v['promotion_id']."_".$v['domain_id']][$Name] += 1;
            }
        }
    }
}