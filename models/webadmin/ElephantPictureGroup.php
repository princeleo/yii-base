<?php
/**
 * DataCenter AgentOrder库
 * Author:ShiYaoJia
 * Class DataCenterModel extends DataCenterModel
 * @package app\models\datacenter
 */

namespace app\models\webadmin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\datacenter\AgentStatisticsInfoModel;

class ElephantPictureGroup extends \app\models\BaseModel
{

    public static function getDb()
    {
        return Yii::$app->get('db_webadmin');
    }

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'elephant_base_group';
    }
}
