<?php

namespace app\models\agent_ids;

use app\models\agent\AgentPicInfo;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "t_agent".
 *
 * @property integer $AgentID
 * @property integer $Balance
 * @property string $Salt
 * @property string $ProvideType
 * @property string $AccountType
 * @property string $BeginDate
 * @property string $EndDate
 * @property integer $PriceGroupID
 * @property integer $WinhiFlag
 * @property integer $AllowLogin
 */
class Agent extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_agent';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_agent');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AgentID'], 'required'],
            [['AgentID', 'Balance', 'PriceGroupID', 'WinhiFlag', 'AllowLogin'], 'integer'],
            [['ProvideType', 'AccountType'], 'string'],
            [['BeginDate', 'EndDate'], 'safe'],
            [['Salt'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AgentID' => '自增ID',
            'Balance' => '预付款余额',
            'Salt' => '代理商随机加密串',
            'ProvideType' => '供应方式(代理,OEM)',
            'AccountType' => '结算方式(预付款,后付费月结)',
            'BeginDate' => '代理开始时间',
            'EndDate' => '代理结束时间',
            'PriceGroupID' => '价格组ID',
            'WinhiFlag' => '汇海标示（是否为汇海的分公司 0：不是 1：是）',
            'AllowLogin' => '是否允许登陆系统标志',
        ];
    }

}
