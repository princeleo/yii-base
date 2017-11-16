<?php

namespace app\models\shop;

use Yii;

/**
 * This is the model class for table "customer_speedpost_callback".
 *
 * @property integer $id
 * @property integer $type
 * @property string $call_func
 * @property string $callback_content
 * @property integer $status
 * @property integer $created
 * @property integer $modified
 */
class CustomerSpeedpostCallback extends \yii\db\ActiveRecord
{
    const STATUS_DEFAULT = 0; //默认
    const STATUS_SUCCESS = 1;//执行成功
    const STATUS_FAIL = -1;//未执行成功

    const TYPE_STATUS_SYNC = 1;//同步状态
    const TYPE_RATE_UPDATE = 2; //费率更新

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_speedpost_callback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'call_func', 'callback_content','key'], 'required','on' => 'create'],
            [['type', 'status', 'created', 'modified'], 'integer'],
            [['call_func'], 'string', 'max' => 200],
            [['callback_content'], 'string', 'max' => 1000],
            [['key'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'call_func' => 'Call Func',
            'callback_content' => 'Callback Content',
            'status' => 'Status',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }
}
