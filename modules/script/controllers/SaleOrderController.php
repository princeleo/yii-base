<?php

namespace app\modules\script\controllers;

use app\common\log\Log;
use app\models\agent\AgentBase;
use app\models\baseboss\ShopSalesOrderModel;
use app\models\datacenter_statistics\StatSaleOrder;
use app\models\shop\ShopOrderRefund;
use app\models\shop\ShopProductVersion;
use yii\data\Pagination;


/**
 * 销售订单统计脚本
 * Class SaleOrderController
 * @package app\modules\script\controllers
 */
class SaleOrderController extends BaseController{
    public $layout  = false;


    /**
     * 脚本入口
     * @param null $startDate
     * @param null $endDate
     */
    public function actionRun($startDate=null, $endDate=null)
    {
        date_default_timezone_set('PRC');
        set_time_limit(0);

        $startDate = $startDate ? strtotime($startDate) : strtotime(date('Y-m-d 00:00:00', strtotime('-1 days')));
        $endDate = $endDate ? strtotime($endDate)+86399 : strtotime(date('Y-m-d 23:59:59', strtotime('-1 days')));
        Log::info('start | exec time['.microtime(true).'] | params['.json_encode(func_get_args()).']', __METHOD__);
        $this->statOrder($startDate,$endDate);
        Log::info('end | exec time['.microtime(true).']', __METHOD__);
        echo "执行完成！！";
    }


    /**
     * 统计销售订单
     * @param $startDate
     * @param $endDate
     */
    protected  function statOrder($startDate,$endDate)
    {
        while($startDate < $endDate){
            $list = ShopSalesOrderModel::find()->where(['pay_status' => ShopSalesOrderModel::PAY_STATUS_SUCCEED])
                ->andFilterWhere(['>=','pay_time',$startDate])
                ->andFilterWhere(['<=','pay_time',$startDate + 86399 ])
                ->leftJoin(AgentBase::tableName(),AgentBase::tableName().'.agent_id = '.ShopSalesOrderModel::tableName().'.agent_id')
                ->leftJoin(ShopProductVersion::tableName(),ShopProductVersion::tableName().'.id = '.ShopSalesOrderModel::tableName().'.shop_product_version_id')
                ->select('total_order_amount,actual_amount,shop_sales_order.setup_fee as setup_fee,software_service_fee,hardware_purchase_cost,shop_sales_order.agent_id as agent_id,shop_sales_order.id,agent_base.short_name as name,agent_base.domain as domain,shop_product_version.version_type as version_type')
                ->asArray()->all();
            $refund = ShopOrderRefund::find()->where(['audit_status' => ShopOrderRefund::REFUND_AUDIT_APPROVAL,'refund_status' => ShopOrderRefund::REFUND_SUCCESS])
                ->andFilterWhere(['>=','refund_time',$startDate])
                ->andFilterWhere(['<=','refund_time',$startDate + 86399 ])
                ->select('SUM(refund_amount) AS refund_amount,SUM(refund_val) AS refund_val,agent_id')
                ->groupBy(['agent_id'])->indexBy('agent_id')->asArray()->all();

            $data = [];
            foreach($list as &$li){
                $data[$li['agent_id']][$li['version_type']] = [
                    'agent_name' => $li['name'],
                    'domain' => empty($li['domain']) ? '' : ','.$li['domain'].',',  //服务商所属区域
                    'shop_nums' => empty($data[$li['agent_id']][$li['version_type']]['shop_nums']) ? 1 : $data[$li['agent_id']][$li['version_type']]['shop_nums']+1, //商户数
                    'order_amount' => empty($data[$li['agent_id']][$li['version_type']]['order_amount']) ? $li['total_order_amount'] : $data[$li['agent_id']][$li['version_type']]['order_amount'] + $li['total_order_amount'], //订单金额
                    'pay_amount' => empty($data[$li['agent_id']][$li['version_type']]['pay_amount']) ? $li['actual_amount'] : $data[$li['agent_id']][$li['version_type']]['pay_amount'] + $li['actual_amount'],//支付金额
                    'setup_amount' => empty($data[$li['agent_id']][$li['version_type']]['setup_amount']) ? $li['setup_fee'] : $data[$li['agent_id']][$li['version_type']]['setup_amount'] + $li['setup_fee'],//开户费
                    'service_amount' => empty($data[$li['agent_id']][$li['version_type']]['service_amount']) ? $li['software_service_fee'] : $data[$li['agent_id']][$li['version_type']]['service_amount'] + $li['software_service_fee'],//平台服务费
                    'hardware_amount' => empty($data[$li['agent_id']][$li['version_type']]['hardware_amount']) ? $li['hardware_purchase_cost'] : $data[$li['agent_id']][$li['version_type']]['hardware_amount'] + $li['hardware_purchase_cost'],//硬件费
                    'order_nums' => empty($data[$li['agent_id']][$li['version_type']]['order_nums']) ? 1 : $data[$li['agent_id']][$li['version_type']]['order_nums']+1,//订单数
                    'refund_amount' => !empty($refund[$li['agent_id']][$li['version_type']]) ? $refund[$li['agent_id']][$li['version_type']]['refund_amount'] : 0, //退款金额
                    'refund_val' => !empty($refund[$li['agent_id']][$li['version_type']]) ? $refund[$li['agent_id']][$li['version_type']]['refund_val'] : 0, //退款天数
                ];
                unset($li);
            }
            unset($li,$list);

            //保存数据
            $date = date('Y-m-d',$startDate);
            foreach($data as $agent_id=>$data){
                foreach($data as $version_type => $li){
                    $model = StatSaleOrder::find()->where(['stat_date' => $date,'agent_id' => $agent_id,'version_type' => $version_type])->one();
                    $model = empty($model) ? new StatSaleOrder() : $model;
                    $li['agent_id'] = $agent_id;
                    $li['version_type'] = $version_type;
                    $li['stat_date'] = $date;
                    $li['order_amount'] = intval($li['order_amount']);
                    $li['pay_amount'] = intval($li['pay_amount']);
                    $li['setup_amount'] = intval($li['setup_amount']);
                    $li['service_amount'] = intval($li['service_amount']);
                    $li['hardware_amount'] = intval($li['hardware_amount']);
                    if(!$model->load(['StatSaleOrder' => $li]) || !$model->save()){
                        Log::error('保存失败 || '.json_decode($model->errors),$li,__METHOD__);
                    }
                }
            }
            unset($data);

            //循环控制
            $startDate = $startDate + 86400;
        }
    }
}