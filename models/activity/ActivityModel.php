<?php

namespace app\models\activity;

use app\models\shop\Customer;
use Yii;
use yii\data\ActiveDataProvider;


class ActivityModel extends \app\models\BaseModel
{


    const TIME_TYPE_DAT = 1; //按天
    const TIME_TYPE_RANGE = 2; //按时间段


    const ACTIVITY_TYPE_MJ = 1;
    public static $activityType = [
        self::ACTIVITY_TYPE_MJ => '满减活动',
    ];
    /**
     * 启用状态
     */
    const STATUS_OPEN = 1; //开启
    const STATUS_CLOSE = 2; //关闭
    const STATUS_DELETE = 3; //删除
    public static $enableStatus = [
        self::STATUS_OPEN => '开启',
        self::STATUS_CLOSE => '关闭',
    ];


    /**
     * 活动状态
     */
    const ACTIVITY_STATUS_NO_START = 1; //未开始
    const ACTIVITY_STATUS_ING = 2; //活动中
    const ACTIVITY_STATUS_EXPIRED = 3; //已过期
    public static $activityStatus = [
        self::ACTIVITY_STATUS_NO_START => '未开始',
        self::ACTIVITY_STATUS_ING => '活动中',
        self::ACTIVITY_STATUS_EXPIRED => '已过期',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'activity_type','time_type', 'status',  'created','modified','gateway_activity_id'], 'integer'],
            [['app_id','name','activity_desc','activity_time','shop_id'], 'string'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(ActivityRule::className(), ['activity_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopTime()
    {
        return $this->hasMany(ActivityShopTime::className(), ['activity_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAudit()
    {
        return $this->hasMany(ActivityAudit::className(), ['activity_id' => 'id']);
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ActivityModel::find()->where(['<>','status',3]);
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['ActivityModel'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($params['app_id'])) {
            $query->andFilterWhere([ActivityModel::tableName().'.app_id' => $params['app_id']]);
        }

        if(!empty($params['created_s']) && !empty($params['created_e'])){
            $query->andFilterWhere(['>=','start_time', strtotime($params['created_s'])]);
            $query->andFilterWhere(['<=','end_time', strtotime($params['created_e'])]);
            //$query->innerJoin(ActivityShopTime::tableName(), ActivityModel::tableName().".id = ".ActivityShopTime::tableName().".activity_id");
        }
        if (!empty($params['activity_name'])) {
            $query->andFilterWhere(['like',ActivityModel::tableName().'.name' , $params['activity_name']]);
        }
        //$query->orderBy('id desc');
        return $dataProvider;
    }


    /**
     * 名称唯一
     * @param $params
     * @return bool
     */
    public static function findNameUnique($params){

        $name = isset($params['name'])?$params['name']:"";
        $query = ActivityModel::find()->where("name=:name", [':name' => $name]);

        if(isset($params['activity_id'])){
            $query->andFilterWhere(['<>',ActivityModel::tableName().'.id', $params['activity_id']]);
        }
        $data = $query->asArray()->one();
        if(empty($data)){
            return false;
        }
        return true;
    }

    /**
     * 判断当前活动时间的活动状态
     */
    public static function checkActivityStatus($activity_id){
        $expired = $unstart = 0;
        $today_time = date('Y-m-d');
        $model = ActivityModel::findOne($activity_id);
        $activity_time = explode(',',$model['activity_time']);
        $count = count($activity_time);

        if($model['time_type'] == self::TIME_TYPE_DAT){
            //按天
            foreach($activity_time as $value){
                if($value < $today_time){
                    $expired ++;
                }elseif($value > $today_time){
                    $unstart ++;
                }
            }
            if($count == $unstart){
                return self::ACTIVITY_STATUS_NO_START;
            }elseif($count == $expired){
                return self::ACTIVITY_STATUS_EXPIRED;
            }else{
                return self::ACTIVITY_STATUS_ING;
            }
        }else{
            //时间段
            $start_time = $activity_time[0];
            $end_time = $activity_time[1];
            if($start_time <= $today_time && $end_time >= $today_time ){
                return self::ACTIVITY_STATUS_ING;
            }elseif($start_time < $today_time && $end_time < $today_time ){
                return self::ACTIVITY_STATUS_EXPIRED;
            }else{
                return self::ACTIVITY_STATUS_NO_START;
            }
        }
    }

}
