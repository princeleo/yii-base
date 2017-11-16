<?php

namespace app\models\agent;

use Yii;

/**
 * This is the model class for table "agent_audit".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $status
 * @property string $remark
 * @property string $operator_name
 * @property integer $operator_id
 * @property integer $created
 */
class AgentAudit extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_audit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'status', 'operator_id'], 'required'],
            [['agent_id', 'status', 'operator_id', 'created'], 'integer'],
            [['remark'], 'string', 'max' => 200],
            [['operator_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agent_id' => 'Agent ID',
            'status' => 'Status',
            'remark' => 'Remark',
            'operator_name' => 'Operator Name',
            'operator_id' => 'Operator ID',
            'created' => 'Created',
        ];
    }
}
