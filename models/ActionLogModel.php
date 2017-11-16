<?php
/**
 * Author: Richard <chenz@snsshop.cn>
 * Date: 2016/11/28
 * Time: 17:46
 */

namespace app\models;

use yii\db\ActiveRecord;

class ActionLogModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%action_log}}';
    }
}