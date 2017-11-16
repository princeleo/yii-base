<?php

namespace app\models\agent;

use Yii;

/**
 * This is the model class for table "agent_bank".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $account_type
 * @property string $account_name
 * @property string $account_number
 * @property string $account_bank
 */
class AgentBank extends \app\models\BaseModel
{
    const ACCOUNT_TYPE_PUBLIC = 1;
    const ACCOUNT_TYPE_PRIVATE = 2;

    public static function account_type()
    {
        return [
            self::ACCOUNT_TYPE_PUBLIC => '对公账号',
            self::ACCOUNT_TYPE_PRIVATE => '对私账号'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'account_name', 'account_number', 'account_bank'], 'required'],
            [['agent_id', 'account_type','created','modified'], 'integer'],
            [['account_name'], 'string', 'max' => 50],
            [['account_number'], 'string', 'max' => 50],
            [['account_bank'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'agent_id' => '服务商ID',
            'account_type' => '账户类型：1对公，2对私',
            'account_name' => '账号名称',
            'account_number' => '卡号',
            'account_bank' => '银行名称',
            'modified' => '更新时间',
            'created' => '创建时间'
        ];
    }


    /**
     * 获取服务商银行账户
     * @param $agent_id
     * @return array
     */
    public function getAgentBank($agent_id)
    {
        $query = $this->find()->where(['agent_id'=>$agent_id])->one();
        return $this->recordToArray($query);
    }
}
