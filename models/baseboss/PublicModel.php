<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\baseboss;

use Yii;
use app\models\BaseModel;
use app\common\ResultModel;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

class PublicModel extends BaseModel
{
    public $resultModel;

    public function init()
    {
        parent::init();
        $this->resultModel = new ResultModel();
    }

    /**
     * 数据库
     *
     * @return mixed
     */
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    /**
     *  插入多条
     * @param $tableName
     * @param $field
     * @param $value
     * @return array
     */
    public function batchInsert($tableName, array $field, array $value)
    {
        self::getDb()->createCommand()->batchInsert($tableName, $field, $value)->execute();
    }

    /**
     *  翻页
     * @param $query
     * @param $limit
     * @return array
     */
    public function page($query, $limit)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        $result = $this->resultModel->result($dataProvider->getModels(),$dataProvider->getPagination());

        $totalCount = empty($result['retData']['pagination']['total_count']) ? 0 : $result['retData']['pagination']['total_count'];
        $pageSize = empty($result['retData']['pagination']['per_page']) ? 1 : $result['retData']['pagination']['per_page'];
        $data['pages'] = new Pagination(['totalCount' =>$totalCount, 'pageSize' => $pageSize]);
        $data['lists'] = empty($result['retData']['lists']) ? [] : $result['retData']['lists'];
        return $data;
    }
}