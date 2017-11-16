<?php
/**
 * DataCenter AgentOrder库
 * Author:ShiYaoJia
 * Class DataCenterModel extends DataCenterModel
 * @package app\models\datacenter
 */

namespace app\models\datacenter_statistics;

use Yii;

class ScriptLog extends DataCenterStatisticsModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'data_script_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'controller', 'action', 'datetime', 'result', 'created'], 'required']
        ];
    }
}
