<?php
/**
 * DataCenter 基础库
 * Author:ShiYaoJia
 * Class DataCenterModel extends ActiveRecord
 * @package app\models\datacenter
 */

namespace app\models\datacenter_source;

use Yii;
use app\models\BaseModel;
use app\common\ResultModel;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;


class DataCenterSourceModel extends BaseModel
{
    public $resultModel;

    public function init()
    {
        parent::init();
        $this->resultModel = new ResultModel();
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_datacenter_source');
    }


    /**
     *  插入多条
     * @param $tableName
     * @param $field
     * @param $value
     * @return array
     */
    public static function batchInsert($tableName, array $field, array $value)
    {
        self::getDb()->createCommand()->batchInsert($tableName, $field, $value)->execute();
    }

    /**
     *  翻页
     * @param $query
     * @param $limit
     * @param $page
     * @return array
     */
    public function page($query, $limit, $page)
    {
        $count = count($this->findBySql($query->createCommand()->getRawSql())->asArray()->all());
        $page = $page < 0 ? 0 : $page;
        $page = $page > ceil($count/$limit) ? (int)(ceil($count/$limit) - 1) : (int)$page;
        $data['pages']['totalCount'] = $count;
        $data['pages']['pageSize'] = $limit;
        $data['pages']['currentPage'] = $page;
        $query->offset($page*$limit)->limit($limit);
        $sql = $query->createCommand()->getRawSql();
        $data['lists'] = $this->findBySql($sql)->asArray()->all();
        return $data;
    }
}
