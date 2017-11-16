<?php

namespace app\models\activity;

use Yii;
use yii\data\ActiveDataProvider;


class ActivityRule extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cost_undertake',  'apply_shop', 'apply_order', 'user_num_limit' ,'reduction_gear','created','modified','activity_id'], 'integer'],
            [['reduction_fields'], 'string'],
        ];
    }
}
