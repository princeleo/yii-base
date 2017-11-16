<?php
/**
 * Author: leo.zou
 * Date: 2017/03/22
 * Time: 15:18
 * boss 4.0 大象平台流水
 */

namespace app\modules\script\controllers;

use app\common\helpers\BaseHelper;
use app\common\helpers\ConstantHelper;
use app\common\log\Log;
use app\common\services\BaseVariableService;
use app\common\services\MessageService;
use app\models\baseboss\ShopProductVersionModel;
use app\models\baseboss\ShopSalesOrderModel;
use app\models\shop\ShopBase;
use app\models\shop\ShopOrder;
use Yii;


/**
 * 每日日报——发送邮件
 * Class DailyReportController
 * @package app\modules\script\controllers
 */
class DailyReportController extends BaseController {
    public $TimeInterval = 86400; // 时间间隔  默认一天
    public $Model;  // Model
    public $Data = [];   // Data
    public $shopOrderModel;
    public $date;

    /**
     * 每日邮件汇报交易数据,默认为前一天
     * 调用方式： php yii <route>
     * 例子：php yii script/daily-report/run 2017-09-10
     * 本地 bossdev.cc/admin/tools/index?controller=DailyReport&action=Run&date=2017-09-10
     * @param string $date
     */
    public function actionRun($date=null)
    {
        date_default_timezone_set('PRC');
        set_time_limit(0);

        $mailDate = empty($date) ? date("Y-m-d", strtotime('-1 day')) : $date;
        Log::info('start | exec time['.microtime(true).'] | params['.json_encode(func_get_args()).']', __METHOD__);

        $mailService = new MessageService();
        $baseVar = new BaseVariableService();
        $emailTitle = "【运营日报】大象点餐平台运营数据日报（" . $mailDate . "）";
        $mail = $baseVar->getVariable(BaseVariableService::DAILY_REPORT_EMAIL_MAIN);//获取收信地址列表-主收件人
        $mail = json_decode($mail, TRUE);
        $mailList = $baseVar->getVariable(BaseVariableService::DAILY_REPORT_EMAIL_LIST);//抄送收件人
        $mailList = json_decode($mailList, TRUE);

        $this->initDate($mailDate); //初始化日期
        $content = $this->getReportData($mailDate);
        if(YII_ENV != CODE_RUNTIME_ONLINE){
            echo $content;exit();
        }
        $mailService->send_email($emailTitle, $content, $mail, $mailList);//发送邮件

        Log::info('end | exec time['.microtime(true).']', __METHOD__);
        echo "执行完成！！";
    }


    /**
     * 日报数据源
     * @param $date
     * @return array
     */
    private function getReportData($date)
    {
        $tpl = "<p><b>【运营日报】大象点餐平台运营日报</b></p>
                <p><b>统计周期：{$date} 00:00:00-23:59:59</b></p>
                <p><b>数据来源：BOSS系统-数据中心</b></p>
                <br>
                <br>";

        $tpl .= "<p><b>1、昨日交易数据总览</b></p>";
        $tpl .= "<p><b>（1）大象点餐基础版</b></p>";
        $tpl .= $this->statOrder(ShopProductVersionModel::VERSION_TYPE_Q);
        $tpl .= "<p><b>（2）大象点餐增强版</b></p>";
        $tpl .= $this->statOrder(ShopProductVersionModel::VERSION_TYPE_W);

        $tpl .= "<p><b>2、昨日销售额排行-服务商TOP20</b></p>";
        $tpl .= $this->agentSaleTop20();

        $tpl .= "<p><b>3、昨日扫码点餐订单数排行-服务商TOP20</b></p>";
        $tpl .= $this->agentTop20();

        $tpl .= "<p><b>4、昨日扫码点餐订单数排行-城市TOP20</b></p>";
        $tpl .= $this->cityTop20();

        $tpl .= "<p><b>5、昨日扫码点餐订单数排行-自营商户TOP10</b></p>";
        $tpl .= $this->shopTop10();

        $tpl .= "<p><b>6、昨日销售数据总览</b></p>";
        $tpl .= $this->lastSale();

        return $tpl;
    }


    /**
     * 昨日销售数据总览
     */
    private function lastSale()
    {
        $sql = "SELECT FROM_UNIXTIME(sale.pay_time,'%%Y-%%m-%%d') AS pay_date,sale.shop_product_version_id AS version_id,
                version.name,
                COUNT(sale.id) AS order_count,
                SUM(sale.setup_fee) AS setup_fee,
                SUM(sale.software_service_fee) AS service_fee,
                SUM(sale.hardware_purchase_cost) AS hardware_fee,
                SUM(sale.total_order_amount) AS total_amount
                FROM shop_sales_order AS sale
                LEFT JOIN shop_product_version AS `version` ON version.id = sale.shop_product_version_id
                WHERE sale.pay_status=2 AND sale.pay_time >= %s AND sale.pay_time<= %s GROUP BY sale.shop_product_version_id";

        $command_sql = '';
        foreach($this->dateTimeConf() as $k => $date){ //拼凑SQL
            if($k == 1 || $k == 2 || $k == 3) $command_sql .= " UNION ALL ";
            $command_sql .= vsprintf($sql,$date);
        }
        unset($sql);


        //整理数据
        $data = $temp = $total = [];
        $version_list = ShopProductVersionModel::find()->where(['status' => ShopProductVersionModel::NORMAL])->indexBy('name')->asArray()->all();
        foreach($version_list as $li){
            $temp[$li['name']]= [];
        }
        unset($version_list,$li);
        $date_conf = $this->dateConf();
        $list = ShopSalesOrderModel::findBySql($command_sql)->asArray()->all();
        foreach($list as $li){
            if($li['pay_date'] == date('Y-m-d',$this->shopOrderModel->date['start_time'])){
                $temp[$li['name']][$li['pay_date']] = [
                    $li['name'],
                    $li['order_count'],
                    BaseHelper::amountFenToYuan($li['setup_fee']),
                    BaseHelper::amountFenToYuan($li['service_fee']),
                    BaseHelper::amountFenToYuan($li['hardware_fee']),
                    BaseHelper::amountFenToYuan($li['total_amount'])
                ];
                $total[$li['name']] = $li['total_amount'];
            }else{
                $y = empty($total[$li['name']]) ? 0 : $total[$li['name']];
                $temp[$li['name']][$li['pay_date']] = $li['total_amount'] == 0 ? '暂无对比数据' : ($y-$li['total_amount'])/$li['total_amount'];
            }
        }
        foreach($temp as $name => $t){
            $v = array();
            foreach($this->dateTimeConf() as $k => $date){
                $date = date('Y-m-d',$date['start_time']);
                if($k == 0){
                    $v = isset($t[$date]) ? array_merge($v,$t[$date]) : [$name,0,'0.00','0.00','0.00','0.00'];
                }else{
                    $v = isset($t[$date]) ? array_merge($v,[$this->formatNum($t[$date])]) : array_merge($v,['暂无对比数据']);
                }
            }
            if($v[5] > 0){
                array_unshift($data,$v);
            }else{
                $data[] = $v;
            }
        }
        unset($temp,$name,$t,$v,$date,$total);

        //渲染表格
        $head = [
            [['v' => '销售套餐名称','rowspan' => 2],['v' => '昨日销售额数据','colspan' => 5],['v'=> '数据对比','colspan'=>3]],
            [['v'=>'销售套餐数'],['v'=>'开户费'],['v'=>'开户费'],['v'=>'设备采购费'],['v'=>'总计'],['v'=>'较前日<br/>('.$date_conf[1].')'],['v'=>'较上周同期<br/>('.$date_conf[2].')'],['v'=>'较四周前同期<br/>('.$date_conf[3].')']]
        ];
        return $data = $this->createTable($head,$data);
    }


    /**
     * 昨日销售数据总览
     * version_type：1大象点餐基础版,2大象点餐增强版
     * @param int $version_type
     * @return string
     */
    protected function statOrder($version_type = 1)
    {
        //自营|测试|非自营SQL
        $sql1 = "SELECT '%s' AS data_type,a.agent_id,a.order_type,FROM_UNIXTIME(a.paid_time,'%%Y-%%m-%%d') as pay_date,COUNT(a.id) AS pay_count,SUM(a.paid_amount) AS pay_amount FROM (
                    SELECT shop_order.* FROM shop_order
                    LEFT JOIN shop_base ON shop_base.shop_id =  shop_order.shop_id
                    WHERE shop_order.pay_status = 2 AND shop_order.app_id = ".ConstantHelper::PLATFORM_ELEPHANT."
                    AND paid_time >= '%s' AND paid_time <= '%s' AND version_type = ".$version_type."
                ) AS a LEFT JOIN agent_base ON agent_base.agent_id = a.agent_id WHERE 1=1";

        //较前日|较上周同期|较四周前同期SQL
        $sql2 = "SELECT '' AS data_type,shop_base.agent_id,shop_order.order_type,FROM_UNIXTIME(shop_order.paid_time,'%%Y-%%m-%%d') as pay_date,COUNT(shop_order.id) AS pay_count,SUM(shop_order.paid_amount) AS pay_amount FROM shop_order
                    LEFT JOIN shop_base ON shop_base.shop_id =  shop_order.shop_id
                    WHERE shop_order.pay_status = 2 AND shop_order.app_id = ".ConstantHelper::PLATFORM_ELEPHANT."
                    AND paid_time >= '%s' AND paid_time <= '%s' AND version_type = ".$version_type."
                    GROUP BY shop_order.order_type";

        $zy = YII_ENV == CODE_RUNTIME_ONLINE ? 10 : 34; //自营
        $cs = YII_ENV == CODE_RUNTIME_ONLINE ? 9 : 37; //测试
        $n_zy = YII_ENV == CODE_RUNTIME_ONLINE ? [10,9] : [34,37];//非自营
        $data_type = ['zy' => $zy,'n_zy' => $n_zy,'cs' => $cs];
        $date_conf = $this->dateConf();
        $data = $temp = array();
        $command_sql = '';

        //装载自营测试非自营SQL
        foreach($data_type as $index => $value){
            if($index != 'zy') $command_sql .= " UNION ALL ";
            $command_sql .= vsprintf($sql1,array_merge([$index],$this->shopOrderModel->date));
            if($index == 'n_zy'){
                $command_sql .= " AND agent_base.domain_new NOT LIKE '%,{$value[0]},%' AND agent_base.domain_new NOT LIKE '%,{$value[1]},%'";
            }else{
                $command_sql .= " AND agent_base.domain_new LIKE '%,{$value},%'";
            }
            $command_sql .=" GROUP BY a.order_type";
            $temp[$index] = [  //初始化
                'pay_amount' => 0,
                'pay_count' => 0,
                'scan_amount' => 0,
                'scan_count' => 0
            ];
        }
        //装载较前日|较上周同期|较四周前同期SQL
        foreach($this->dateTimeConf() as $k => $date){
            if($k == 0) continue;
            $command_sql .= " UNION ALL ";
            $command_sql .= vsprintf($sql2,$date);
            $temp[date('Y-m-d',$date['start_time'])] = [
                'pay_amount' => 0,
                'pay_count' => 0,
                'scan_amount' => 0,
                'scan_count' => 0
            ];
        } //echo $command_sql;die;
        //针对data_type不同（自营，非自营，测试）三种数据处理
        //根据不同时间来区别不同时期的数据，根据不同订单类型判断扫码点餐
        $list = ShopSalesOrderModel::findBySql($command_sql)->asArray()->all();
        unset($command_sql,$sql1,$sql2,$date,$zy,$cs,$n_zy);
        foreach($list as $li){
            if(!empty($li['data_type'])){
                $temp[$li['data_type']]['pay_amount'] = $temp[$li['data_type']]['pay_amount'] + $li['pay_amount'];
                $temp[$li['data_type']]['pay_count'] = $temp[$li['data_type']]['pay_count'] + $li['pay_count'];
                if($li['order_type'] == 0){
                    $temp[$li['data_type']]['scan_amount'] = $temp[$li['data_type']]['scan_amount'] + $li['pay_amount'];
                    $temp[$li['data_type']]['scan_count'] = $temp[$li['data_type']]['scan_count'] + $li['pay_count'];
                }
            }else{
                $temp[$li['pay_date']]['pay_amount'] = $temp[$li['pay_date']]['pay_amount'] + $li['pay_amount'];
                $temp[$li['pay_date']]['pay_count'] = $temp[$li['pay_date']]['pay_count'] + $li['pay_count'];
                if($li['order_type'] == 0){
                    $temp[$li['pay_date']]['scan_amount'] = $temp[$li['pay_date']]['scan_amount'] + $li['pay_amount'];
                    $temp[$li['pay_date']]['scan_count'] = $temp[$li['pay_date']]['scan_count'] + $li['pay_count'];
                }
            }
        }


        //整理数据结构
        $rows = ['pay_amount' => '实付订单总金额','pay_count' => '实付订单总数','scan_amount' => '扫码点餐实付订单金额','scan_count' => '扫码点餐实付订单数'];
        foreach(['pay_amount','pay_count','scan_amount','scan_count'] as $key){
            $arr = array($rows[$key]);
            $total = 0;
            foreach($data_type as $index => $type){
                if(in_array($key,['pay_count','scan_count'])){
                    $arr[] = !empty($temp[$index][$key]) ? $temp[$index][$key] : 0;
                }else{
                    $arr[] = !empty($temp[$index][$key]) ? BaseHelper::amountFenToYuan($temp[$index][$key]) : BaseHelper::amountFenToYuan(0);
                }

                $total = !empty($temp[$index][$key]) ? $total + $temp[$index][$key] : $total;
            }
            $arr[] = in_array($key,['pay_count','scan_count']) ? $total : BaseHelper::amountFenToYuan($total);
            foreach($date_conf as $k => $date){
                if($k == 0) continue;
                $arr[] = empty($temp[$date][$key]) ? '暂无对比数据' : $this->formatNum(($total-$temp[$date][$key])/$temp[$date][$key]);
            }

            $data[] = $arr;
        }
        unset($arr,$total,$key,$temp,$data_type,$index,$k);

        //渲染表格
        $head = [
            [['v' => $version_type == 1 ? '基础版交易数据' : '增强版交易数据','rowspan' => 2],['v' => '昨日交易数据','colspan' => 4],['v'=> '数据对比','colspan'=>3]],
            [['v'=>'自营'],['v'=>'非自营'],['v'=>'测试'],['v'=>'总计'],['v'=>'较前日<br/>('.$date_conf[1].')'],['v'=>'较上周同期<br/>('.$date_conf[2].')'],['v'=>'较四周前同期<br/>('.$date_conf[3].')']]
        ];
        return $data = $this->createTable($head,$data);
    }

    /**
     * 昨日扫码点餐订单数排行-城市TOP20
     */
    protected function cityTop20()
    {
        $sql = "SELECT customer.city_id,customer.city_text AS city,COUNT(shop_order.id) AS order_count,SUM(shop_order.paid_amount) AS pay_amount FROM shop_order
                LEFT JOIN shop_base ON shop_base.shop_id = shop_order.shop_id
                LEFT JOIN customer ON shop_base.customer_id = customer.id
                WHERE shop_order.order_type = 0 AND shop_order.pay_status = 2 AND shop_order.app_id = ".ConstantHelper::PLATFORM_ELEPHANT."
                AND paid_time >= '".$this->shopOrderModel->date['start_time']."' AND paid_time <= '".$this->shopOrderModel->date['end_time']."'
                GROUP BY customer.city_text  ORDER BY order_count DESC  LIMIT 20";
        $list = ShopSalesOrderModel::findBySql($sql)->asArray()->all();


        //整理数据
        $data = [];
        foreach($list as $k => $li){
            $data[] = [
                $k+1,
                $li['city'],
                $li['order_count'],
                BaseHelper::amountFenToYuan($li['pay_amount'])
            ];
        }

        //渲染表格
        $head = [
            [['v' => '排名'],['v' => '城市'],['v' => '扫码点餐实付订单数'],['v' => '扫码点餐实付订单金额']]
        ];
        return $data = $this->createTable($head,$data);
    }


    /**
     * 昨日扫码点餐订单数排行-服务商TOP20
     * @return string
     */
    protected function agentTop20()
    {
        $sql = "SELECT agent_base.agent_id,agent_base.short_name AS agent_name,COUNT(shop_order.id) AS order_count,SUM(shop_order.paid_amount) AS pay_amount FROM shop_order
                LEFT JOIN agent_base ON agent_base.agent_id = shop_order.agent_id
                WHERE shop_order.order_type = 0 AND shop_order.pay_status = 2 AND shop_order.app_id = ".ConstantHelper::PLATFORM_ELEPHANT."
                AND paid_time >= '".$this->shopOrderModel->date['start_time']."' AND paid_time <= '".$this->shopOrderModel->date['end_time']."'
                GROUP BY agent_base.agent_id ORDER BY order_count DESC  LIMIT 20";
        $list = ShopSalesOrderModel::findBySql($sql)->asArray()->all();


        //整理数据
        $data = [];
        foreach($list as $k => $li){
            $data[] = [
                $k+1,
                $li['agent_name'],
                $li['order_count'],
                BaseHelper::amountFenToYuan($li['pay_amount'])
            ];
        }

        //渲染表格
        $head = [
            [['v' => '排名'],['v' => '服务商名称'],['v' => '扫码点餐实付订单数'],['v' => '扫码点餐实付订单金额']]
        ];
        return $data = $this->createTable($head,$data);
    }

    /**
     * 昨日销售额排行-服务商TOP20
     * @return string
     */
    protected function agentSaleTop20()
    {
        $sql = "SELECT agent_base.short_name AS agent_name,
                COUNT(sale.id) AS order_count,
                SUM(sale.setup_fee) AS setup_fee,
                SUM(sale.software_service_fee) AS service_fee,
                SUM(sale.hardware_purchase_cost) AS hardware_fee,
                SUM(sale.total_order_amount) AS total_amount
                FROM shop_sales_order AS sale
                LEFT JOIN agent_base ON agent_base.agent_id = sale.agent_id
                WHERE sale.pay_status=2 AND sale.pay_time >= ".$this->shopOrderModel->date['start_time']." AND sale.pay_time<= ".$this->shopOrderModel->date['end_time']."
                GROUP BY sale.agent_id  ORDER BY total_amount DESC  LIMIT 20";
        $list = ShopSalesOrderModel::findBySql($sql)->asArray()->all();

        //整理数据
        $data = [];
        foreach($list as $k => $li){
            $data[] = [
                $k+1,
                $li['agent_name'],
                $li['order_count'],
                BaseHelper::amountFenToYuan($li['setup_fee']),
                BaseHelper::amountFenToYuan($li['service_fee']),
                BaseHelper::amountFenToYuan($li['hardware_fee']),
                BaseHelper::amountFenToYuan($li['total_amount'])
            ];
        }

        //渲染表格
        $head = [
            [['v' => '排名'],['v' => '服务商名称'],['v' => '销售套餐数'],['v' => '开户费'],['v' => '平台服务费'],['v' => '设备采购费'],['v' => '总计']]
        ];
        return $data = $this->createTable($head,$data);
    }


    /**
     * 昨日扫码点餐订单数排行-自营商户TOP10
     * @return string
     */
    protected function shopTop10()
    {
        $zy = YII_ENV == CODE_RUNTIME_ONLINE ? 10 : 34;
        $sql = "SELECT a.* FROM (
                    SELECT shop_base.agent_id,shop_base.shop_id,shop_base.name AS shop_name,COUNT(shop_order.id) AS order_count,SUM(shop_order.paid_amount) AS pay_amount FROM shop_order
                    LEFT JOIN shop_base ON shop_base.shop_id =  shop_order.shop_id
                    WHERE shop_order.order_type = 0 AND shop_order.pay_status = 2 AND shop_order.app_id = ".ConstantHelper::PLATFORM_ELEPHANT."
                    AND paid_time >= '".$this->shopOrderModel->date['start_time']."' AND paid_time <= '".$this->shopOrderModel->date['end_time']."'
                    GROUP BY shop_base.shop_id ORDER BY order_count DESC
                ) AS a LEFT JOIN agent_base ON agent_base.agent_id = a.agent_id WHERE agent_base.domain_new LIKE '%,".$zy.",%' LIMIT 10";
        $list = ShopSalesOrderModel::findBySql($sql)->asArray()->all();


        //整理数据
        $data = [];
        foreach($list as $k => $li){
            $data[] = [
                $k+1,
                $li['shop_name'],
                $li['order_count'],
                BaseHelper::amountFenToYuan($li['pay_amount'])
            ];
        }

        //渲染表格
        $head = [
            [['v' => '排名'],['v' => '商户名称（简称）'],['v' => '扫码点餐实付订单数'],['v' => '扫码点餐实付订单金额']]
        ];
        return $data = $this->createTable($head,$data);
    }


    /**
     * 返回四个时期配置
     * 当天 | 较前日 | 较上周同期 | 较四周前同期
     * @return array
     */
    protected function dateTimeConf()
    {
        return [$this->shopOrderModel->date,$this->shopOrderModel->eveDate,$this->shopOrderModel->weekDate,$this->shopOrderModel->monthDate];
    }

    protected function dateConf()
    {
        return [
            date('Y-m-d',$this->shopOrderModel->date['start_time']),
            date('Y-m-d',$this->shopOrderModel->eveDate['start_time']),
            date('Y-m-d',$this->shopOrderModel->weekDate['start_time']),
            date('Y-m-d',$this->shopOrderModel->monthDate['start_time'])
        ];
    }

    /**
     * 生成表格
     * @param array $head
     * @param array $data
     * @param int $border
     * @return string
     */
    protected function createTable($head = array(),$data = array(),$border = 1)
    {
        $table = '<table border='.$border.' cellpadding=5>';
        foreach($head as $tr){
            $table .= '<tr>';
            foreach($tr as $th){
                $table .= '<th '.(empty($th['rowspan']) ? '' : ' rowspan='.$th['rowspan'].'').(empty($th['colspan']) ? '' : ' colspan='.$th['colspan'].'').'>'.$th['v'].'</th>';
            }
            $table .= '</tr>';
        }
        foreach($data as $li){
            $table .= '<tr>';
            foreach($li as $td){
                if(!is_string($td) && !is_numeric($td)) $td = ' - ';
                $table .= '<td align=center>'.$td.'</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table><br/><br/>';
        return $table;
    }

    /**
     * 格式百分比数据
     * @param $num
     * @return string
     */
    protected function formatNum($num)
    {
        $num = round($num,4);
        if($num == 0){
            $msg = '→';
        }elseif($num > 0){
            $msg = '↑';
        }else{
            $msg = '↓';
        }

        return (abs($num)*100).'%'.$msg;
    }


    /**
     * 查询日期数据初始化
     * @param $date
     */
    private function initDate($date = null)
    {
        $this->shopOrderModel = new ShopOrder();
        if($date) {
            //指定日期
            $this->shopOrderModel->date = [
                'start_time' => strtotime($date . ' 00:00:00'),
                'end_time' => strtotime($date . ' 23:59:59'),
            ];
            //前一天
            $this->shopOrderModel->eveDate = [
                'start_time' => $this->shopOrderModel->date['start_time'] - 86400,
                'end_time' => $this->shopOrderModel->date['end_time']  - 86400,
            ];
            //上周同期
            $this->shopOrderModel->weekDate = [
                'start_time' => $this->shopOrderModel->date['start_time'] - (86400 * 7),
                'end_time' => $this->shopOrderModel->date['end_time']  - (86400 * 7),
            ];
            //4周前同期
            $this->shopOrderModel->monthDate = [
                'start_time' => $this->shopOrderModel->date['start_time'] - (86400 * 28),
                'end_time' => $this->shopOrderModel->date['end_time']  - (86400 * 28),
            ];
        } else {
            $this->shopOrderModel->date = [
                'start_time' => strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))),
                'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 days')))
            ];
            $this->shopOrderModel->eveDate = [
                'start_time' => strtotime(date('Y-m-d 00:00:00', strtotime('-2 days'))),
                'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime('-2 days')))
            ];
            $this->shopOrderModel->weekDate = [
                'start_time' => strtotime(date('Y-m-d 00:00:00', strtotime('-1 week -1 days'))),
                'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 week -1 days')))];
            $this->shopOrderModel->monthDate = [
                'start_time' => strtotime(date('Y-m-d 00:00:00', strtotime('-4 week -1 days'))),
                'end_time' => strtotime(date('Y-m-d 23:59:59', strtotime('-4 week -1 days')))];
        }
    }
}