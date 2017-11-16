<?php

namespace app\models\activity;

use Yii;
use yii\data\ActiveDataProvider;


class ActivityShopTime extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_shop_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'activity_id',  'shop_id', 'activity_rule_id','created','modified'], 'integer'],
            [['activity_time'], 'string'],
        ];
    }
}
