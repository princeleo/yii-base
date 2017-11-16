<?php

namespace app\models\activity;

use Yii;
use yii\data\ActiveDataProvider;


class ActivityOrder extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_order';
    }
}
