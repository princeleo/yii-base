<?php

namespace app\models\agent;

use Yii;

/**
 * This is the model class for table "agent_contract".
 *
 * @property integer $id
 * @property string $contract_no
 * @property string $contract_name
 * @property string $images
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $agent_id
 * @property integer $customer_id
 * @property integer $created
 */
class AgentContract extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_contract';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'contract_name', 'start_time', 'end_time', 'created'], 'required'],
            [['images'], 'string'],
            [['start_time', 'end_time', 'agent_id', 'customer_id', 'created'], 'integer'],
            [['contract_no', 'contract_name'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contract_no' => 'Contract No',
            'contract_name' => 'Contract Name',
            'images' => 'Images',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'agent_id' => 'Agent ID',
            'customer_id' => 'Customer ID',
            'created' => 'Created',
        ];
    }
}
