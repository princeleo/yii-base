<?php

namespace app\models\agent;

use Yii;

/**
 * This is the model class for table "agent_duty".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $is_main
 * @property string $name
 * @property string $position
 * @property string $mobile
 * @property string $tel
 * @property string $email
 * @property integer $qq
 * @property string $fax_phone
 * @property string $remark
 * @property integer $modified
 * @property integer $created
 */
class AgentDuty extends \app\models\BaseModel
{
    //职位
    public static function getPosition()
    {
        return [
            1 => '销售',
            2 => '客服',
            3 => '运营',
            4 => '美工',
            5 => '合同管理',
            6 => '财务',
            7 => '其他'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_duty';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'name', 'mobile'], 'required'],
            [['agent_id', 'is_main', 'modified', 'created'], 'integer'],
            [['name', 'mobile', 'tel', 'fax_phone'], 'string', 'max' => 50],
            [['position', 'email'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 200],
            [['qq'], 'string', 'max' => 20],
            [['qq'], 'number', 'integerOnly' => true, 'message' => 'QQ只能包含数字'],
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
            'is_main' => 'Is Main',
            'name' => 'Name',
            'position' => 'Position',
            'mobile' => 'Mobile',
            'tel' => 'Tel',
            'email' => 'Email',
            'qq' => 'Qq',
            'fax_phone' => 'Fax Phone',
            'remark' => 'Remark',
            'modified' => 'Modified',
            'created' => 'Created',
        ];
    }
}
