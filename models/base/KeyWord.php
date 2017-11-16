<?php

namespace app\models\base;

use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class KeyWord extends BaseModel
{
    const DELETED_DEFAULT = 1;
    const DELETED_DISABLE = 2;
    const DELETED_TRUE = 3;

    public static function getDeteled()
    {
        return [
            self::DELETED_DEFAULT => '正常',
            self::DELETED_DISABLE => '禁用',
            self::DELETED_TRUE => '删除'
        ];
    }


    /**链接类型
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%base_key_word}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['name', ], 'string'],
            [['modified', 'created','deleted'], 'integer'],
        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['deleted' => 1]);
        if (isset($params['name']) && $params['name']) {
            $query->andFilterWhere(['like', 'name', $params['name']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }



}
