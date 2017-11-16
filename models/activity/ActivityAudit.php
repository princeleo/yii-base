<?php

namespace app\models\activity;

use Yii;
use yii\data\ActiveDataProvider;


class ActivityAudit extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_audit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'activity_id','created','modified'], 'integer'],
            [['activity_time','action_name','action_desc','shop_id'], 'string'],
        ];
    }
}
