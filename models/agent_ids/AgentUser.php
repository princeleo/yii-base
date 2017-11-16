<?php

namespace app\models\agent_ids;

use Yii;

/**
 * This is the model class for table "t_agent_user".
 *
 * @property integer $PID
 * @property string $UserName
 * @property integer $AgentID
 * @property string $LoginName
 * @property string $Password
 * @property string $AroID
 * @property integer $LoginCount
 * @property string $LoginDate
 * @property string $LoginIP
 * @property integer $IsAdmin
 * @property integer $Status
 * @property string $CreateDate
 */
class AgentUser extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_agent_user';
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
            [['AgentID', 'LoginCount', 'IsAdmin', 'Status'], 'integer'],
            [['LoginDate', 'CreateDate'], 'safe'],
            [['UserName', 'LoginName', 'Password', 'LoginIP'], 'string', 'max' => 50],
            [['AroID'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'PID' => '自增ID',
            'UserName' => '用户名称',
            'AgentID' => '代理商ID',
            'LoginName' => '登陆账户',
            'Password' => '登陆密码',
            'AroID' => '角色ID序列',
            'LoginCount' => '登陆次数',
            'LoginDate' => '最后登陆时间',
            'LoginIP' => '最后登陆IP',
            'IsAdmin' => '是否管理员',
            'Status' => '0:正常，1：锁定',
            'CreateDate' => '建创日期',
        ];
    }
}
