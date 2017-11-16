<?php
/**
 * Created by PhpStorm.
 * User: lk2015
 * Date: 2016/12/30
 * Time: 14:37
 */

namespace app\common\services;

use app\common\errors\BaseError;
use app\common\exceptions\BusinessException;
use app\common\log\ActionLog;
use app\common\ResultModel;
use app\models\agent\AgentSettleReport;
use app\models\base\SettleGroup;
use app\models\shop\ConsumeOrder;
use app\models\shop\ShopOrder;
use app\models\shop\ShopSubPaymentSettings;
use yii\base\Component;
use Yii;
use yii\base\Exception;

class SyncCommissionService extends Component
{
    private $agentSettleService = null;

    /**
     * 分佣类型
     * @var null
     */
    private $commissionTypes = null;

    /**
     * @param $list
     * @param $params
     * @return bool
     */
    public function batchSyncOrderCommission($list, $params)
    {
        if (!empty($list) && is_array($list)) {
            foreach ($list as $item) {
                $serviceParams =[
                    'agent_id' => $item['agent_id'],
                    'shop_id' => $item['shop_id'],
                    'shop_sub_id' => $item['shop_sub_id'],
                    'paid_time_s' => $params['paid_time_s'],
                    'paid_time_e' => $params['paid_time_e'],
                    'commission_type' => AgentSettleReport::APP_TYPE_ORDER_COMMISSION,
                    'action_type' => 1,
                    'rate' => $item['cur_rate'],
                ];
                $this->syncCommission($serviceParams);
            }
        }
        return true;
    }

    /**
     * 同步分佣金额入口
     * @param array $params
     * @return bool
     * @throws BusinessException
     */
    public function syncCommission($params=[])
    {
        $params = $this->checkParams($params);
        $res = $this->syncCommissionByType($params);
        $state = (!isset($res['code']) || $res['code'] < 0) ? ActionLog::FAILED : ActionLog::SUCCESS;
        Yii::$app->alog->record($this->getActLogResourceType(), $this->getActLogResourceId($params['action_type']), ActionLog::SYNC, $params, $res['result'], $state);
        return true;
    }

    /**
     * 同步分佣金额入口
     * @param array $params
     * @return bool
     * @throws BusinessException
     */
    public function syncCommissionRetry($params, $list, $ext=[])
    {
        $ext = $this->checkParams($ext);
        list($searchModel, $searchParams, $logicFun) = $this->getSearchModel($ext);
        if (!method_exists($this, $logicFun)) {
            throw new BusinessException(BaseError::PARAMETER_ERR);
        }
        $this->$logicFun($list, $params, $ext);
        return true;
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    private function checkParams($params)
    {
        //参数校验
        if (empty($params['action_type']) || !in_array($params['action_type'], [1,2])) {//1:录入费率  2：同步分佣金额
            throw new BusinessException(BaseError::PARAMETER_ERR);
        }
        if ($params['action_type'] == 1 ) {//1:录入费率
            if (!isset($params['rate']) || $params['rate'] < 0 || $params['rate'] >100) {
                throw new BusinessException(BaseError::PARAMETER_ERR);
            }
            $params['rate'] = number_format($params['rate'], 2, '.', '');
        } else {
            $params['rate'] = 0;
        }

        //校验分佣类型
        $this->getCommissionTypes();
        if (!isset($params['commission_type']) || ($params['commission_type'] !== 0 && !array_key_exists($params['commission_type'], $this->commissionTypes))){
            throw new BusinessException(BaseError::PARAMETER_ERR);
        }

        $default = [
            'agent_id' => null,
            'agent_name' => null,
            'shop_id' => null,
            'shop_name' => null,
            'shop_sub_id' => null,
            'shop_sub_name' => null,
            'paid_time_s' => null,
            'paid_time_e' => null,
            'rate' => 0,
            'page' => 0,
            'page_size' => 500,
            'exec_time' => microtime(true)
        ];

        $params = array_merge($default, $params);
        !empty($params['skey'])  OR  $params['skey'] = md5($params['agent_id'].":".$params['shop_id'].":".$params['shop_sub_id'].":".$params['paid_time_s'].":".$params['paid_time_e'].":".$params['rate'].":".$params['exec_time']);
        return $params;
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    private function getSearchModel($params)
    {
        switch ($params['commission_type']) {
            case AgentSettleReport::APP_TYPE_ORDER_COMMISSION:
                $searchModel = new ShopOrder();
                $searchParams = $params;
                $logicFun = 'batchUpdateShopOrderCommission';
                break;
            case AgentSettleReport::APP_TYPE_CONSUME_COMMISSION:
                $searchModel = new ConsumeOrder();
                $searchParams = $params;
                $logicFun = 'batchUpdateConsumeOrderCommission';
                break;
            default:
                throw new BusinessException(BaseError::PARAMETER_ERR);
        }
        return [$searchModel, $searchParams, $logicFun];
    }

    /**
     * @param array $params
     * @return array
     * @throws BusinessException
     */
    private function syncCommissionByType($params=[])
    {
        set_time_limit(0);
        Yii::info('syncCommissionByType | step 1 | start |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);

        $page = $params['page'];
        $onePage = $page ? true : false;
        $totalPage = 1;
        $ret = [
            'code' => 0,
            'result' => []
        ];
        unset($params['agent_name']);
        unset($params['shop_name']);
        unset($params['shop_sub_name']);

        $resultModel = new ResultModel();

        list($searchModel, $searchParams, $logicFun) = $this->getSearchModel($params);
        if (!method_exists($this, $logicFun)) {
            throw new BusinessException(BaseError::PARAMETER_ERR);
        }
        $successCount = $failedCount = 0;
        while ($page < $totalPage || $onePage) {

            $onePage = false;
            $searchParams['page'] = $page;
            Yii::info('syncCommissionByType | step 2 | loop '.$params['page'].' | start |  skey['.$params['skey'].'] | exec time['.microtime(true).']', __METHOD__);
            $page++;

            //取数据列表
            $dataProvider = $searchModel->search($searchParams);
            $dataProvider->setPagination(['pageSize' => $searchParams['page_size'],'page' => $searchParams['page']]);
            $result = $resultModel->result($dataProvider->getModels(),$dataProvider->getPagination());

            //设置分页
            if ($params['page'] == 0) {
                $totalCount = empty($result['retData']['pagination']['total_count']) ? 0 : $result['retData']['pagination']['total_count'];
                Yii::info('syncCommissionByType | step 2.1 | pagination['.json_encode($result['retData']['pagination']).'] | params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
                $totalPage = ceil($totalCount / $params['page_size']);
                if (!$totalPage) {
                    $ret['result'][] = $this->formatLog('total_count', $totalCount);
                    break;
                }
            }

            //处理数据
            if (empty($result['retData']['lists'])) {
                $ret['result'][] = $this->formatLog('list_empty', $searchParams);
                Yii::info('syncCommissionByType | step 2.2 | loop '.$searchParams['page'].' | empty |  skey['.$params['skey'].'] | exec time['.microtime(true).']', __METHOD__);
                continue;
            }

            $res = $this->$logicFun($result['retData']['lists'], ['rate'=>$params['rate'], 'action_type' => $params['action_type']], $params);
            if ($res['code'] < 0) {
                $ret['code'] = -1;
                $ret['result'][] = $this->formatLog('failed', $res['data']);
                $failedCount += count($res['data']);
            } else {
                $successCount += count($res['data']);
            }
            unset($result);
            Yii::info('syncCommissionByType | step 2.3 | loop '.$params['page'].' | start |  skey['.$params['skey'].'] | exec time['.microtime(true).']', __METHOD__);
        }
        $ret['result'] += [$this->formatLog('success_count', $successCount), $this->formatLog('failed_count', $failedCount)];
        Yii::info('syncCommissionByType | step 1 | start |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
        return $ret;
    }

    /**
     * 更新订单分佣金额
     * @param $list
     * @param $params
     * @return bool
     */
    private function batchUpdateShopOrderCommission($list, $params, $ext=[])
    {
        if (is_null($this->agentSettleService)) {
            $this->agentSettleService = new AgentSettleService();
        }
        $ids = [];
        $sql = 'update '.ShopOrder::tableName().' set ';
        $rateStr = '';
        $factorageStr = '';
        $commissionStr = '';
        $updateInfo = [];
        foreach ($list as $key => $item) {
            $rate = $item['rate'];
            $factorage = $item['factorage'];
            if ($params['action_type'] == 1) { //录入费率 更新费率和手续费
                $rate = $params['rate'];
                $factorage = number_format($item['paid_amount']*$rate/100, 6, '.', '');
            }
            $commission = $this->agentSettleService->getCommission($item['agent_id'],$item['shop_sub_id'],$item['order_no'],$rate,$item['paid_amount'],$item['paid_time'],SettleGroup::SETTLE_TYPE_TRANS,$item['app_id']);
            $ids[] = $item['id'];
            $updateInfo[$item['id']] = [
                'o_r' => $item['rate'],
                'n_r' => $rate,
                'o_f' => $item['factorage'],
                'n_f' => $factorage,
                'o_c' => $item['commission'],
                'n_c' => $commission,
            ];
            $rateStr .= ' when '.$item['id'].' then '.$rate;
            $factorageStr .= ' when '.$item['id'].' then '.$factorage;
            $commissionStr .= ' when '.$item['id'].' then '.$commission;
        }
        if ($rateStr) {
            $sql .= 'rate = case id '.$rateStr.' end,';
        }
        if ($factorageStr) {
            $sql .= 'factorage = case id '.$factorageStr.' end,';
        }
        if ($commissionStr) {
            $sql .= 'commission = case id '.$commissionStr.' end,';
        }

        $sql = trim($sql, ',');

        $sql .= ' where id in ('.implode(',', $ids).');';

        $ret = [
            'code' => 0,
            'data' => $ids
        ];
        $transaction = ShopOrder::getDb()->beginTransaction();
        try{
            Yii::$app->db->createCommand($sql)->query();
            $transaction->commit();
            Yii::$app->alog->record('commission_detail', $ext['skey'], ActionLog::SYNC, $params, [$this->formatLog('success_count', count($updateInfo))], ActionLog::SUCCESS, ['ext' => $ext, 'list' => $list, 'update_info'=>$updateInfo]);
            Yii::info('syncCommissionType1 | updateShopOrderCommission | success |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
        } catch (Exception $e) {
            $transaction->rollBack();
            $ret['code'] = -1;
            Yii::$app->alog->record('commission_detail', $ext['skey'], ActionLog::SYNC, $params, [$this->formatLog('failed_count', count($updateInfo)),$this->formatLog('failed', $e->getMessage())], ActionLog::FAILED, ['ext' => $ext, 'list' => $list, 'update_info'=>$updateInfo]);
            Yii::info('syncCommissionType1 | updateShopOrderCommission | failed |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
        }
        return $ret;
    }

    /**
     * 更新订单分佣金额
     * @param $list
     * @param $params
     * @return bool
     */
    private function batchUpdateConsumeOrderCommission($list, $params, $ext=[])
    {
        if (is_null($this->agentSettleService)) {
            $this->agentSettleService = new AgentSettleService();
        }
        $ids = [];
        $sql = 'update '.ConsumeOrder::tableName().' set ';
        $commissionStr = '';
        $updateInfo = [];
        foreach ($list as $key => $item) {
            $commission = $this->agentSettleService->getCommission($item['agent_id'], 0, '', 0, $item['cost'],$item['consume_date'], SettleGroup::SETTLE_TYPE_CONSUME,$item['app_id']);
            $ids[] = $item['id'];
            $updateInfo[$item['id']] = [
                'o_c' => $item['commission'],
                'n_c' => $commission,
            ];
            $commissionStr .= ' when '.$item['id'].' then '.$commission;
        }

        if ($commissionStr) {
            $sql .= 'commission = case id '.$commissionStr.' end,';
        }

        $sql = trim($sql, ',');

        $sql .= ' where id in ('.implode(',', $ids).');';

        $ret = [
            'code' => 0,
            'data' => $ids
        ];
        $transaction = ConsumeOrder::getDb()->beginTransaction();
        try{
            Yii::$app->db->createCommand($sql)->query();
            $transaction->commit();
            Yii::$app->alog->record('commission_detail', $ext['skey'], ActionLog::SYNC, $params, [$this->formatLog('success_count', count($updateInfo))], ActionLog::SUCCESS, ['ext' => $ext, 'list' => $list, 'update_info'=>$updateInfo]);
            Yii::info('syncCommissionType1 | updateShopOrderCommission | success |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
        } catch (Exception $e) {
            $transaction->rollBack();
            $ret['code'] = -1;
            Yii::$app->alog->record('commission_detail', $ext['skey'], ActionLog::SYNC, $params, [$this->formatLog('failed_count', count($updateInfo)),$this->formatLog('failed', $e->getMessage())], ActionLog::FAILED, ['ext' => $ext, 'list' => $list, 'update_info'=>$updateInfo]);
            Yii::info('syncCommissionType1 | updateShopOrderCommission | failed |  params['.json_encode($params).'] | exec time['.microtime(true).']', __METHOD__);
        }
        return $ret;
    }

    /**
     * 日志resource_type
     */
    private function getActLogResourceType()
    {
        return 'commission';
    }

    /**
     * 日志resource_id
     * @param $actionType
     * @return string
     * @throws BusinessException
     */
    private function getActLogResourceId($actionType)
    {
        switch ($actionType) {
            case 1: //录入费率
                return 'sync_his_rate';
                break;
            case 2: //同步分佣金额
                return 'sync_commission';
                break;
            default:
                throw new BusinessException(BaseError::PARAMETER_ERR);
        }
    }

    /**
     * @param $type
     * @param $params
     * @return array
     */
    private function formatLog($type, $params)
    {
        switch ($type) {
            case 'list_empty':
                return [
                    'errorMsg' => '列表数据为空',
                    'params' => $params,
                ];
                break;
            case 'failed':
                return '失败原因：['.$params.']';
                break;
            case 'success_count':
                return  '共有 ['.$params.'] 条记录执行成功';
                break;
            case 'failed_count':
                return  '共有 ['.$params.'] 条记录执行失败';
                break;
            case 'total_count':
                return  '共有 ['.$params.'] 条记录满足条件';
                break;
            default:
                return [];
        }
    }

    /**
     * 取分分佣类型
     */
    private function getCommissionTypes()
    {
        $this->commissionTypes = AgentSettleReport::getApp_type();
    }
}