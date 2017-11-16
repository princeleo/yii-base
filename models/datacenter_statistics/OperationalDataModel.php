<?php
/**
 * DataCenterStatistics OperationalData 库
 * Author:ShiYaoJia
 * Class OperationalDataModel extends DataCenterStatisticsModel
 * @package app\models\datacenter_statistics
 */

namespace app\models\datacenter_statistics;

use Yii;

class OperationalDataModel extends DataCenterStatisticsModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'operational_data';
    }
}
