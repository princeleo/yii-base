<?php
namespace app\models\bank_channel;

use app\models\BaseModel;
use yii\behaviors\TimestampBehavior;

/**
 * Class Rate
 *
 * @package \\${NAMESPACE}
 */
class Rate extends BaseModel
{
    public static function tableName()
    {
        return '{{%bank_channel_rates}}';
    }

    public function rules()
    {
        return [
            [['bank_id', 'name', 'rate', 'status'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique', 'targetAttribute' => ['bank_id', 'name']],
            [['status'], 'in', 'range' => [0, 1]],
            [['rate'], 'number', 'max' => 100, 'min' => 0],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'modified',
            ],
        ];
    }

    public function getBank()
    {
        return $this->hasOne(Bank::className(), ['id' => 'bank_id']);
    }
}
