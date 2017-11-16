<?php

namespace app\models\agent;
use app\models\BaseModel;
use app\models\user\AuthUser;
use yii\data\ActiveDataProvider;
use yii;
class AgentSettleMonth extends BaseModel
{

    const SETTLE_AUDIT_STAY_STATUS = 1;//结算单待审核
    const SETTLE_AUDIT_PASS_STATUS = 2;//结算单审核通过
    const TECH_HANDLE_STAY_STATUS = 3;//待技术处理
    const TECH_HANDLE_PASS_STATUS = 4;//技术处理完成

    const PROBLEM_DEFAULT_STATUS = 0;//默认值
    const PROBLEM_STAY_STATUS = 1;//异议待审核
    const PROBLEM_NOT_PASS_STATUS = 2;//异议审核不通过
    const PROBLEM_PASS_STATUS = 3;//异议审核通过
    const PROBLEM_AFFIRM_STATUS = 5;//异议通过
    const PROBLEM_TECH_HANDLE_PASS_STATUS = 4;//技术处理完成
    const PROBLEM_NOT_AFFIRM_STATUS = 6;//异议不通过

    const PROOF_DEFAULT_STATUS = 0; //默认值
    const PROOF_STAY_STATUS = 1; //凭证待审核
    const PROOF_NOT_PASS_STATUS = 2;//凭证审核不通过
    const PROOF_PASS_STATUS = 3; //已完成


    const SETTLE_NOT_PROBLEMS = 1; //无异议
    const SETTLE_YES_PROBLEMS = 2;//有异议


    const AGENT_APPLY_CASH = 1; //服务商累计应付款明细表
    const AGENT_SETTLE_REPORT = 2;//服务商分佣结算对账表


    public static  function getReportType(){
        return [
            self::AGENT_APPLY_CASH =>'服务商累计应付款明细表',
            self::AGENT_SETTLE_REPORT =>'服务商分佣结算对账表',
        ];
    }


    public static  function getIsProblems(){
        return [
            self::SETTLE_NOT_PROBLEMS =>'无异议',
            self::SETTLE_YES_PROBLEMS =>'有异议',
        ];
    }



    /**结算单审核集合
     * @return array
     */
    public static  function getAuditStatus(){
        return [
            self::SETTLE_AUDIT_STAY_STATUS =>'结算单待审核',
            self::SETTLE_AUDIT_PASS_STATUS =>'结算单审核通过',
            self::TECH_HANDLE_STAY_STATUS  =>'待技术处理',
            self::TECH_HANDLE_PASS_STATUS =>'技术处理完成',
        ];
    }
    /**异议审核状态
     * @return array
     */
    public static  function getProblemStatus(){
        return [
            self::PROBLEM_STAY_STATUS =>'异议待审核',
            self::PROBLEM_NOT_PASS_STATUS =>'异议审核不通过',
            self::PROBLEM_PASS_STATUS =>'异议审核通过',
            self::PROBLEM_TECH_HANDLE_PASS_STATUS => '技术处理完成',
            self::PROBLEM_AFFIRM_STATUS => '异议已处理',
            self::PROBLEM_NOT_AFFIRM_STATUS => '待技术处理'
        ];
    }

    /**凭证审核状态
     * @return array
     */
    public static  function getProofStatus(){
        return [
            self::PROOF_STAY_STATUS =>'凭证待审核',
            self::PROOF_NOT_PASS_STATUS =>'凭证审核不通过',
            self::PROOF_PASS_STATUS =>'凭证审核通过',
        ];
    }






    /**月账单
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%agent_settle_month}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['key_date','agent_name','month_no'], 'string'],
            [['agent_id', 'commany_revenue','refund_money', 'status','trade_money','play_agent_money','trade_money','created','modified','audit_status','proof_status','problem_status','is_problems','start_time','end_time'], 'integer'],

        ];
    }

    /**
     * 自动更新created_date和updated_date时间
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'modified'
            ],
        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->with('agentBase');;
        if (isset($params['agent_name']) && $params['agent_name']) {
            $query->andFilterWhere(['like', 'agent_name', $params['agent_name']]);
        }
        if(!empty($params['agent_id'])){
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }

        if(!empty($params['key_date'])){
            $query->andFilterWhere(['key_date' => $params['key_date']]);
        }

        if(isset($params['audit_status']) && $params['audit_status']== self::TECH_HANDLE_STAY_STATUS){
            $query->orFilterWhere(['audit_status' => $params['audit_status']]);
            $query->orFilterWhere(['problem_status' => self::PROBLEM_PASS_STATUS]);
            $query->orFilterWhere(['problem_status' => self::PROBLEM_NOT_AFFIRM_STATUS]);
        }else if(isset($params['audit_status']) && $params['audit_status']== self::SETTLE_AUDIT_STAY_STATUS) {
             $query->andFilterWhere(['audit_status' => self::PROBLEM_DEFAULT_STATUS]);
            $query->orFilterWhere(['audit_status' => self::TECH_HANDLE_PASS_STATUS]);
            $query->orFilterWhere(['problem_status' => self::PROBLEM_TECH_HANDLE_PASS_STATUS]);
          }


    if (isset($params['month_no']) && $params['month_no']) {
        $query->andFilterWhere(['month_no' => $params['month_no']]);
    }

        if (isset($params['problem_status']) && $params['problem_status']) {
            $query->andFilterWhere(['problem_status' => $params['problem_status']]);
        }
        if (isset($params['proof_status']) && $params['proof_status']) {
            $query->andFilterWhere(['proof_status' => $params['proof_status']]);
        }



        if(!empty($params['year'])&&!empty($params['month']))
        {
            if($params['month'] < 10)
            {
                $date = $params['year'].'0'.$params['month'];
            }
            else
            {
                $date = $params['year'].$params['month'];
            }

//            $year=  date("Y", strtotime("+1 months", strtotime($date)));
//            $month=  date("m", strtotime("+1 months", strtotime($date)));
//            $start_time=  $this->mFristAndLast($year,$month)['firstday'];
//            $end_time= $this->mFristAndLast($year,$month)['lastday'];
//            $query->andFilterWhere(['>=', 'created', $start_time]);

            $query->andFilterWhere(['key_date' => $date]);


        }


        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }

    /**
     * 获取指定月份的第一天开始和最后一天结束的时间戳
     *
     * @param int $y 年份 $m 月份
     * @return array(本月开始时间，本月结束时间)
     */
    function mFristAndLast($y = "", $m = ""){
        if ($y == "") $y = date("Y");
        if ($m == "") $m = date("m");
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);

        $m>12 || $m<1 ? $m=1 : $m=$m;
        $firstday = strtotime($y . $m . "01000000");
        $firstdaystr = date("Y-m-01", $firstday);
        $lastday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));

        return array(
            "firstday" => $firstday,
            "lastday" => $lastday
        );
    }



    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getAgentLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['audit_status' => 2] );
        $query->orWhere(['>','problem_status',0]);


        if (isset($params['agent_id']) && $params['agent_id']) {
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }
        if (isset($params['problem_status']) && $params['problem_status']) {
            $query->andFilterWhere(['problem_status' => $params['problem_status']]);
        }
        if (isset($params['proof_status']) && $params['proof_status']) {
            $query->andFilterWhere(['proof_status' => $params['proof_status']]);
        }
        if (isset($params['key_date']) && $params['key_date']) {
            $query->andFilterWhere(['key_date' => $params['key_date']]);
        }
        if(isset($params['status']) && $params['status'] == 1){
            $query->andFilterWhere(['proof_status' => self::PROOF_PASS_STATUS]);
        }
        if(isset($params['status']) && $params['status'] == -1){
            $query->andWhere(['<>','proof_status',self::PROOF_PASS_STATUS]);
        }


        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }

    public function getAgentBase(){
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    public  function getAgentSettleMonth(){
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id','key_date'=>'key_date']);
    }


    /**根据公告ID回去数据
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
    }



    /**
     * 数据表报中心查询
     *
     * @return array $data 以及页码信息
     */
    public function getListsReport($params, $page = 1, $limit = 10)
    {


        $query = self::find()->with('agentBase');;
        if (isset($params['agent_name']) && $params['agent_name']) {
            $query->andFilterWhere(['like', 'agent_name', $params['agent_name']]);
        }
        if(!empty($params['agent_id'])){
            $query->andFilterWhere(['agent_id' => $params['agent_id']]);
        }

        if (isset($params['agent_val']) && $params['agent_val']) {
            $query->andFilterWhere(['in','agent_id',$params['agent_val'] ]);
        }





        if(!empty($params['date_s'])&&!empty($params['date_e'])){
            $query->andWhere(['>','start_time',$params['date_s']]);
            $query->andWhere(['<','end_time',$params['date_e']]);
        }


        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }



}
